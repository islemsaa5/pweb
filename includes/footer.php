</div> <!-- Fin de .layout -->

<!-- Libs pour Export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="assets/js/pdf-export.js"></script>

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
