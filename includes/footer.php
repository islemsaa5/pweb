</div> <!-- Fin de .layout -->

<script>
// Code Javascript simple pour les modales
function toggleModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        if (modal.style.display === "flex") {
            modal.style.display = "none";
        } else {
            modal.style.display = "flex";
        }
    }
}

// Fermer les modales en cliquant a l'exterieur
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});
</script>

</body>
</html>
