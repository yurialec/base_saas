<template>
    <footer class="py-4 bg-light mt-auto shadow-lg">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">Copyright &copy; Administrativo {{ currentYear }}</div>
                <div>
                    <a href="#">Politica de privacidade</a>
                    &middot;
                    <a href="#">Termos de uso</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'AppFooter',
    computed: {
        currentYear() {
            return new Date().getFullYear();
        }
    }
}
</script>
