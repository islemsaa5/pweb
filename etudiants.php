<?php
require_once 'config.php';
requireLogin();

// Seulement l'admin a accès
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion des Étudiants';
$message = '';
$error = '';

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // AJOUTER UN ÉTUDIANT
    if ($action === 'add') {
        $matricule = clean($_POST['matricule']);
        $nom = clean($_POST['nom']);
        $prenom = clean($_POST['prenom']);
        $date_naissance = clean($_POST['date_naissance']);
        $email = clean($_POST['email']);
        $niveau = clean($_POST['niveau']);
        $section = clean($_POST['section']);
        $password = password_hash('password', PASSWORD_BCRYPT);

        if (empty($matricule) || empty($nom) || empty($prenom) || empty($email)) {
            $error = 'Tous les champs obligatoires doivent être remplis.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO etudiants (matricule, nom, prenom, date_naissance, email, mot_de_passe, niveau, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$matricule, $nom, $prenom, $date_naissance, $email, $password, $niveau, $section]);
                $message = "L'étudiant a été ajouté avec succès.";
            } catch (PDOException $e) {
                $error = ($e->getCode() == 23000) ? "Cet email ou matricule existe déjà." : "Erreur : " . $e->getMessage();
            }
        }
    }
    
    // SUPPRIMER UN ÉTUDIANT
    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "L'étudiant a été supprimé.";
        }
    }
}

// Récupérer la liste des étudiants avec leurs moyennes S1, S2 et Annuelle
$query = "
    SELECT e.*, 
    (SELECT AVG(note) FROM notes n JOIN modules m ON n.module_id = m.id WHERE n.etudiant_id = e.id AND m.semestre = 1) as moy_s1,
    (SELECT AVG(note) FROM notes n JOIN modules m ON n.module_id = m.id WHERE n.etudiant_id = e.id AND m.semestre = 2) as moy_s2,
    (SELECT AVG(note) FROM notes WHERE etudiant_id = e.id) as moy_annuelle,
    (SELECT COUNT(*) FROM notes WHERE etudiant_id = e.id) as nb_notes
    FROM etudiants e 
    ORDER BY e.section ASC, e.nom ASC
";
$etudiants = $pdo->query($query)->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Gestion des Étudiants</h1>
        <p>Administration des sections et des résultats académiques</p>
    </div>

    <?php if ($message): ?>
        <div class="msg-success animate-pop"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg-error animate-pop"><?= $error ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <button class="btn-add" onclick="toggleModal('modalAdd')"><i class="fa-solid fa-plus"></i> Nouvel Étudiant</button>
        <div class="badge" style="background: #2c3e80; color: white;">Total : <?= count($etudiants) ?> étudiants</div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>M1 (S1)</th>
                    <th>M2 (S2)</th>
                    <th>Moyenne</th>
                    <th>Statut</th>
                    <th>Actions</th>
                    <th>État</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $e): ?>
                <?php 
                    $m1 = $e['moy_s1'] !== null ? round($e['moy_s1'], 2) : null;
                    $m2 = $e['moy_s2'] !== null ? round($e['moy_s2'], 2) : null;
                    $annuelle = $e['moy_annuelle'] !== null ? round($e['moy_annuelle'], 2) : null;
                    
                    $status_class = ($annuelle >= 10) ? 'badge-admis' : ($annuelle === null ? '' : 'badge-ajourne');
                    $status_text = ($annuelle >= 10) ? 'Admis' : ($annuelle === null ? 'N/A' : 'Ajourné');
                    
                    // Indicateur d'état : Vert si l'étudiant a des notes (dossier rempli), Gris sinon
                    $etat_color = ($e['nb_notes'] > 0) ? '#28a745' : '#888';
                ?>
                <tr>
                    <td><?= $e['id'] ?></td>
                    <td><?= htmlspecialchars($e['matricule']) ?></td>
                    <td><?= htmlspecialchars($e['nom']) ?></td>
                    <td><?= htmlspecialchars($e['prenom']) ?></td>
                    <td style="text-align: center;"><?= ($m1 !== null) ? $m1 : '-' ?></td>
                    <td style="text-align: center;"><?= ($m2 !== null) ? $m2 : '-' ?></td>
                    <td style="text-align: center; font-weight: bold;"><?= ($annuelle !== null) ? $annuelle : '-' ?></td>
                    <td><?= $status_text ?></td>
                    <td>
                        <div style="display:flex; gap:5px; justify-content: center;">
                            <a href="releve_notes.php?id=<?= $e['id'] ?>" class="btn-action" title="Relevé"><i class="fa-solid fa-file-invoice"></i></a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet étudiant ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn-action delete" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; width: 15px; height: 15px; border-radius: 50%; background: <?= $etat_color ?>; box-shadow: inset 0 0 5px rgba(0,0,0,0.2); vertical-align: middle;"></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($etudiants)): ?>
                <tr>
                    <td colspan="10" class="empty-row">Aucun étudiant trouvé.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal d'ajout -->
<div class="modal-overlay" id="modalAdd" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Ajouter un étudiant</h3>
            <button class="modal-close" onclick="toggleModal('modalAdd')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label>Matricule *</label>
                        <input type="text" name="matricule" required>
                    </div>
                    <div class="form-group">
                        <label>Section *</label>
                        <select name="section">
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C" selected>Section C</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Niveau</label>
                    <select name="niveau">
                        <option value="1ere Année">1ère Année</option>
                        <option value="2eme Année" selected>2ème Année</option>
                        <option value="3eme Année">3ème Année</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="date_naissance">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="toggleModal('modalAdd')">Annuler</button>
                <button type="submit" class="btn-add">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = (modal.style.display === 'none') ? 'flex' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
