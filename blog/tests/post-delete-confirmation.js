const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

// Registra os eventos da aplicação para testar confirmação e cancelamento.
const listeners = {};
let confirmed = false;
let message = '';
const context = {
    document: { addEventListener: (type, listener) => { listeners[type] = listener; } },
    window: { confirm: (text) => { message = text; return confirmed; } },
};
// Executa o JavaScript real com document e confirm simulados, sem abrir navegador.
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../public/assets/js/main.js'), 'utf8'), context);

function submit() {
    let prevented = false;
    listeners.submit({
        target: { matches: (selector) => selector === '[data-confirm-delete]', dataset: { postTitle: 'Título de teste' } },
        preventDefault: () => { prevented = true; },
    });
    return prevented;
}

// Cancelar impede o envio; confirmar permite que o formulário siga para o servidor.
assert.equal(submit(), true);
assert.match(message, /Título de teste/);
confirmed = true;
assert.equal(submit(), false);
console.log('Confirmação de exclusão aprovada.');
