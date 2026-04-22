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

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion des Enseignants';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $matricule = clean($_POST['matricule']);
        $nom = clean($_POST['nom']);
        $prenom = clean($_POST['prenom']);
        $email = clean($_POST['email']);
        $specialite = clean($_POST['specialite']);
        $password = password_hash('password', PASSWORD_BCRYPT);

        if (empty($matricule) || empty($nom) || empty($prenom) || empty($email)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO enseignants (matricule, nom, prenom, email, mot_de_passe, specialite) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$matricule, $nom, $prenom, $email, $password, $specialite]);
                $message = "Enseignant ajouté avec succès.";
            } catch (PDOException $e) {
                $error = "Cet email ou matricule existe déjà.";
            }
        }
    }

    elseif ($action === 'assign') {
        $enseignant_id = (int)$_POST['enseignant_id'];
        $module_id = (int)$_POST['module_id'];
        
        $stmt = $pdo->prepare("UPDATE modules SET enseignant_id = ? WHERE id = ?");
        if ($stmt->execute([$enseignant_id, $module_id])) {
            $message = "Module affecté avec succès !";
        } else {
            $error = "Erreur lors de l'affectation.";
        }
    }

    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $pdo->prepare("DELETE FROM enseignants WHERE id = ?")->execute([$id]);
        $message = "L'enseignant a été supprimé.";
    }
}

$enseignants = $pdo->query("
    SELECT e.*, 
    (SELECT GROUP_CONCAT(CONCAT(intitule, ' (', section, ')') SEPARATOR ', ') FROM modules WHERE enseignant_id = e.id) as mes_modules
    FROM enseignants e 
    ORDER BY e.nom
")->fetchAll();

$all_modules = $pdo->query("SELECT id, intitule, code_module, section FROM modules ORDER BY section, intitule")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Gestion des Enseignants</h1>
        <p>Gérer les profils et les affectations de modules par section</p>
    </div>

    <?php if ($message): ?>
        <div class="msg-success animate-pop"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg-error animate-pop"><?= $error ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 20px;">
        <button class="btn-add" onclick="toggleModal('modalAdd')"><i class="fa-solid fa-plus"></i> Nouvel Enseignant</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom & Prénom</th>
                    <th>Spécialité</th>
                    <th>Modules & Sections Affectés</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enseignants as $e): ?>
                <tr>
                    <td><span class="badge badge-code"><?= htmlspecialchars($e['matricule']) ?></span></td>
                    <td><strong><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></strong></td>
                    <td><?= htmlspecialchars($e['specialite']) ?></td>
                    <td>
                        <div style="max-width: 300px; font-size: 12px; color: #555;">
                            <?= $e['mes_modules'] ? htmlspecialchars($e['mes_modules']) : '<span style="color:#888; font-style:italic;">Aucun module</span>' ?>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex; gap:5px;">
                            <button class="btn-action" style="background: #28a745; color:white; border:none;" 
                                    onclick="openAssignModal(<?= $e['id'] ?>, '<?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?>')">
                                <i class="fa-solid fa-book-open"></i> Affecter
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet enseignant ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button class="btn-action delete"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajout Enseignant -->
<div class="modal-overlay" id="modalAdd" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Nouveau profil enseignant</h3>
            <button class="modal-close" onclick="toggleModal('modalAdd')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Matricule *</label>
                    <input type="text" name="matricule" required>
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
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Spécialité</label>
                    <input type="text" name="specialite">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="toggleModal('modalAdd')">Annuler</button>
                <button type="submit" class="btn-add">Créer le profil</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Affectation Module -->
<div class="modal-overlay" id="modalAssign" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Affecter un module à <span id="ens_name_display"></span></h3>
            <button class="modal-close" onclick="toggleModal('modalAssign')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="enseignant_id" id="target_ens_id">
                
                <div class="form-group">
                    <label>Choisir le module et la section :</label>
                    <select name="module_id" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($all_modules as $m): ?>
                            <option value="<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['intitule']) ?> (Section <?= $m['section'] ?>) - <?= $m['code_module'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p style="font-size: 11px; color:#888;">Note : Vous pouvez affecter plusieurs modules l'un après l'autre.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="toggleModal('modalAssign')">Fermer</button>
                <button type="submit" class="btn-add">Confirmer l'affectation</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = (modal.style.display === 'none') ? 'flex' : 'none';
}

function openAssignModal(id, name) {
    document.getElementById('target_ens_id').value = id;
    document.getElementById('ens_name_display').innerText = name;
    toggleModal('modalAssign');
}
</script>

<?php include 'includes/footer.php'; ?>
