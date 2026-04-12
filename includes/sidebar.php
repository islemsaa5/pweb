<?php
// includes/sidebar.php
$role = $_SESSION['role'];
$page_courante = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <img src="https://upload.wikimedia.org/wikipedia/fr/5/52/USTHB_Logo.png" alt="USTHB Logo" style="width: 70px; height: auto; margin-bottom: 15px; border-radius: 8px; background: white; padding: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
        <h3 style="letter-spacing: 1px;">USTHB</h3>
        <small style="color: #b0b8d1;">Gestion de la Scolarité</small>
    </div>

    <div class="user-info">
        <div class="name"><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></div>
        <div class="role">
            <?php
            if ($role === 'admin') echo 'Administrateur';
            elseif ($role === 'enseignant') echo 'Enseignant';
            elseif ($role === 'etudiant') echo 'Etudiant';
            ?>
        </div>
    </div>

    <nav>
        <?php if ($role === 'admin'): ?>
            <a href="dashboard_admin.php" class="<?= $page_courante === 'dashboard_admin.php' ? 'active' : '' ?>">Tableau de bord</a>
            <a href="etudiants.php" class="<?= $page_courante === 'etudiants.php' ? 'active' : '' ?>">Etudiants</a>
            <a href="enseignants.php" class="<?= $page_courante === 'enseignants.php' ? 'active' : '' ?>">Enseignants</a>
            <a href="modules.php" class="<?= $page_courante === 'modules.php' ? 'active' : '' ?>">Modules</a>
            <a href="notes.php" class="<?= $page_courante === 'notes.php' ? 'active' : '' ?>">Gestion des Notes</a>

        <?php elseif ($role === 'enseignant'): ?>
            <a href="dashboard_enseignant.php" class="<?= $page_courante === 'dashboard_enseignant.php' ? 'active' : '' ?>">Tableau de bord</a>
            <a href="mes_modules.php" class="<?= $page_courante === 'mes_modules.php' ? 'active' : '' ?>">Mes Modules</a>
            <a href="saisie_notes.php" class="<?= $page_courante === 'saisie_notes.php' ? 'active' : '' ?>">Saisir les Notes</a>
            <a href="liste_etudiants.php" class="<?= $page_courante === 'liste_etudiants.php' ? 'active' : '' ?>">Liste des Etudiants</a>

        <?php elseif ($role === 'etudiant'): ?>
            <a href="dashboard_etudiant.php" class="<?= $page_courante === 'dashboard_etudiant.php' ? 'active' : '' ?>">Tableau de bord</a>
            <a href="mes_notes.php" class="<?= $page_courante === 'mes_notes.php' ? 'active' : '' ?>">Mes Notes</a>
            <a href="releve_notes.php" class="<?= $page_courante === 'releve_notes.php' ? 'active' : '' ?>">Releve de Notes</a>
        <?php endif; ?>
    </nav>

    <div class="logout-link">
        <a href="logout.php">Deconnexion</a>
    </div>
</div>
