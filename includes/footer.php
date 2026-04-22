<?php
/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */
<!-- 
 Projet: Gestion de Scolarité USTHB
 Équipe:
 - SAADI Islem (232331698506)
 - KHELLAS Maria (242431486807)
 - ABDELLATIF Sara (242431676416)
 - DAHMANI Anais (242431679715)
-->
</div> <!-- Fin de .layout -->

<!-- Libs pour Export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="assets/js/pdf-export.js"></script>

<script>

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

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});
</script>

</body>
</html>
