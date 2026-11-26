const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

// Exercita os eventos do JavaScript da página sem depender de um navegador.
const listeners = {};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../public/assets/js/main.js'), 'utf8'), {
    document: { addEventListener: (type, listener) => { listeners[type] = listener; } },
});

function field(value) {
    return {
        value,
        message: '',
        setCustomValidity(message) { this.message = message; },
        matches() { return true; },
    };
}

function submit(title, content) {
    const fields = { titulo: field(title), conteudo: field(content) };
    let prevented = false;
    const form = {
        elements: fields,
        matches: (selector) => selector === '[data-post-form]',
        reportValidity: () => Object.values(fields).every((item) => item.message === ''),
    };
    listeners.submit({ target: form, preventDefault: () => { prevented = true; } });
    return { fields, prevented };
}

// Espaços não são conteúdo; textos válidos podem ser enviados.
for (const [title, content] of [['   ', 'Conteúdo'], ['Título', '\n  ']]) {
    assert.equal(submit(title, content).prevented, true);
}
assert.equal(submit('Título', 'Conteúdo').prevented, false);
// Emojis ocupam duas unidades UTF-16, mas cada um conta como um caractere no PHP.
assert.equal(submit('😀'.repeat(255), 'Conteúdo').prevented, false);
assert.equal(submit('😀'.repeat(256), 'Conteúdo').prevented, true);

// Ao editar o campo, a mensagem anterior não deve bloquear a próxima tentativa.
const invalid = submit(' ', 'Conteúdo');
assert.notEqual(invalid.fields.titulo.message, '');
listeners.input({ target: invalid.fields.titulo });
assert.equal(invalid.fields.titulo.message, '');
console.log('Validação JavaScript do formulário aprovada.');
