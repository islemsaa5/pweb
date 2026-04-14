<?php
// includes/sidebar.php
$role = $_SESSION['role'];
$page_courante = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/logo.png" alt="USTHB Logo" style="width: 70px; height: auto; margin-bottom: 15px; border-radius: 8px; background: white; padding: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
        <h3 style="letter-spacing: 1px;">USTHB</h3>
        <small style="color: #b0b8d1;">Scolarité Informatique</small>
    </div>

    <div class="user-info">
        <div class="name"><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></div>
        <div class="role">
            <?php
            if ($role === 'admin') echo 'Administrateur';
            elseif ($role === 'enseignant') echo 'Enseignant';
            elseif ($role === 'etudiant') echo 'Étudiant';
            ?>
        </div>
    </div>

    <nav>
        <?php if ($role === 'admin'): ?>
            <a href="dashboard_admin.php" class="<?= $page_courante === 'dashboard_admin.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="etudiants.php" class="<?= $page_courante === 'etudiants.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-graduate"></i> Étudiants</a>
            <a href="enseignants.php" class="<?= $page_courante === 'enseignants.php' ? 'active' : '' ?>"><i class="fa-solid fa-chalkboard-user"></i> Enseignants</a>
            <a href="modules.php" class="<?= $page_courante === 'modules.php' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i> Modules</a>
            <a href="notes.php" class="<?= $page_courante === 'notes.php' ? 'active' : '' ?>"><i class="fa-solid fa-pen-to-square"></i> Gestion Notes</a>

        <?php elseif ($role === 'enseignant'): ?>
            <a href="dashboard_enseignant.php" class="<?= $page_courante === 'dashboard_admin.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="liste_etudiants.php" class="<?= $page_courante === 'liste_etudiants.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-group"></i> Mes Étudiants</a>
            <a href="saisie_notes.php" class="<?= $page_courante === 'saisie_notes.php' ? 'active' : '' ?>"><i class="fa-solid fa-pen-to-square"></i> Saisie Notes</a>

        <?php elseif ($role === 'etudiant'): ?>
            <a href="dashboard_etudiant.php" class="<?= $page_courante === 'dashboard_etudiant.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Mon Dashboard</a>
            <a href="mes_notes.php" class="<?= $page_courante === 'mes_notes.php' ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list"></i> Mes Notes</a>
            <a href="releve_notes.php" class="<?= $page_courante === 'releve_notes.php' ? 'active' : '' ?>"><i class="fa-solid fa-file-invoice"></i> Relevé Annuel</a>
        <?php endif; ?>
    </nav>

    <div class="logout-link">
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </div>
</div>
