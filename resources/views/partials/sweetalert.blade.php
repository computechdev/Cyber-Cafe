<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Atenção!',
                text: "{{ session('error') }}",
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Aviso!',
                text: "{{ session('warning') }}",
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informação',
                text: "{{ session('info') }}",
                confirmButtonText: 'OK'
            });
        @endif

         const formsConfirmacao = document.querySelectorAll('.form-confirmacao');

        formsConfirmacao.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const titulo = form.dataset.titulo || 'Tem certeza?';
                const texto = form.dataset.texto || 'Essa ação será executada.';
                const botao = form.dataset.botao || 'Sim, continuar';

                Swal.fire({
                    title: titulo,
                    text: texto,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: botao,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });


    });
</script>