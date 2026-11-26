// Valida o formulário de postagem e pede confirmação antes de excluir.
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.matches('[data-post-form]')) {
        // A validação no servidor continua obrigatória mesmo com JavaScript ativo.
        for (const name of ['titulo', 'conteudo']) {
            const field = form.elements[name];
            // Array.from conta caracteres Unicode, como a validação PHP, em vez de unidades UTF-16.
            const tooLong = name === 'titulo' && Array.from(field.value).length > 255;
            field.setCustomValidity(!field.value.trim() ? 'Preencha este campo.' : tooLong ? 'O título deve ter no máximo 255 caracteres.' : '');
        }
        if (!form.reportValidity()) {
            event.preventDefault();
        }
    }
    // A confirmação impede o envio ao cancelar; o servidor ainda valida o token.
    if (form.matches('[data-confirm-delete]')) {
        const title = form.dataset.postTitle;
        if (!window.confirm(`Deseja excluir a postagem "${title}"?`)) {
            event.preventDefault();
        }
    }
});

// Limpa a mensagem de validação quando a pessoa corrige o campo.
document.addEventListener('input', (event) => {
    if (event.target.matches('[data-post-form] [name="titulo"], [data-post-form] [name="conteudo"]')) {
        event.target.setCustomValidity('');
    }
});
