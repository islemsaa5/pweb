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
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE section IN ($placeholders) ORDER BY nom");
    $stmt->execute($sections);
    $etudiants = $stmt->fetchAll();
} else {

    $etudiants = [];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Liste des Etudiants</h1>
        <p>Les etudiants concernes par vos modules (ou tous les etudiants)</p>
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
                    <th>Date de Naissance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['matricule']) ?></td>
                    <td><?= htmlspecialchars($e['nom']) ?></td>
                    <td><?= htmlspecialchars($e['prenom']) ?></td>
                    <td><?= htmlspecialchars($e['niveau']) ?></td>
                    <td><?= htmlspecialchars($e['email']) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($etudiants)): ?>
                <tr><td colspan="5" class="empty-row">Aucun etudiant trouve.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
