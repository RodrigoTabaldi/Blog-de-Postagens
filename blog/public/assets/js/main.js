// Pede confirmação antes de enviar o formulário de exclusão.
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.matches('[data-confirm-delete]')) {
        const title = form.dataset.postTitle;
        if (!window.confirm(`Deseja excluir a postagem "${title}"?`)) {
            event.preventDefault();
        }
    }
});
