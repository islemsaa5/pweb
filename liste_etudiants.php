<?php
/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'enseignant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Liste des Etudiants';
$user_id = $_SESSION['user_id'];

$sec_stmt = $pdo->prepare("SELECT DISTINCT section FROM modules WHERE enseignant_id = ?");
$sec_stmt->execute([$user_id]);
$sections = $sec_stmt->fetchAll(PDO::FETCH_COLUMN);

$etudiants = [];
if (!empty($sections)) {
    $placeholders = str_repeat('?,', count($sections) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT e.*,
        (SELECT SUM(n.note * m.coefficient) / SUM(m.coefficient) FROM notes n JOIN modules m ON n.module_id = m.id WHERE n.etudiant_id = e.id) as moy_annuelle
        FROM etudiants e 
        WHERE section IN ($placeholders) 
        ORDER BY nom
    ");
    $stmt->execute($sections);
    $etudiants = $stmt->fetchAll();
} else {

    $etudiants = [];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Liste des Etudiants</h1>
            <p>Les etudiants concernes par vos modules (ou tous les etudiants)</p>
        </div>
        <input type="text" id="searchInput" placeholder="🔍 Rechercher un étudiant..." style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Niveau</th>
                    <th>Email</th>
                    <th>Moyenne</th>
                    <th>État</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $e): 
                    $annuelle = $e['moy_annuelle'] !== null ? round($e['moy_annuelle'], 2) : null;
                    if ($annuelle === null)      { $etat_class = ''; $etat = 'N/A'; }
                    elseif ($annuelle >= 10)     { $etat_class = 'badge-admis'; $etat = 'ADM'; }
                    else                         { $etat_class = 'badge-ajourne'; $etat = 'AJR'; }
                ?>
                <tr>
                    <td><span class="badge-code"><?= htmlspecialchars($e['matricule']) ?></span></td>
                    <td><strong><?= htmlspecialchars(strtoupper($e['nom'])) ?></strong></td>
                    <td><?= htmlspecialchars(ucfirst(strtolower($e['prenom']))) ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($e['niveau']) ?></td>
                    <td><?= htmlspecialchars($e['email']) ?></td>
                    <td style="text-align:center; font-weight:bold;"><?= $annuelle !== null ? number_format($annuelle, 2) : '-' ?></td>
                    <td style="text-align:center;"><span class="badge <?= $etat_class ?>"><?= $etat ?></span></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($etudiants)): ?>
                <tr><td colspan="7" class="empty-row">Aucun étudiant trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table-container tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('.empty-row')) return;
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
