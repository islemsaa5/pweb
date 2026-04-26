<?php
/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */
$role = $_SESSION['role'];
$page_courante = basename($_SERVER['PHP_SELF']);
?><div class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/logo.png" alt="USTHB Logo" style="width: 45px; height: auto; margin-bottom: 5px; border-radius: 5px; background: white; padding: 3px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
        <h3 style="letter-spacing: 0.5px; font-size: 14px; margin: 0;">USTHB</h3>
        <small style="color: #b0b8d1; font-size: 10px; display: block; margin-top: 2px;">Scolarité Informatique</small>
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
            <a href="dashboard_enseignant.php" class="<?= $page_courante === 'dashboard_enseignant.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="liste_etudiants.php" class="<?= $page_courante === 'liste_etudiants.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-group"></i> Mes Étudiants</a>
            <a href="saisie_notes.php" class="<?= $page_courante === 'saisie_notes.php' ? 'active' : '' ?>"><i class="fa-solid fa-pen-to-square"></i> Saisie Notes</a>

        <?php elseif ($role === 'etudiant'): ?>
            <a href="dashboard_etudiant.php" class="<?= $page_courante === 'dashboard_etudiant.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Mon Dashboard</a>
            <a href="profil_etudiant.php" class="<?= $page_courante === 'profil_etudiant.php' ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> Mon Profil</a>
            <a href="mes_notes.php" class="<?= $page_courante === 'mes_notes.php' ? 'active' : '' ?>"><i class="fa-solid fa-file-invoice"></i> Mes Notes</a>
            <a href="releve_notes.php" class="<?= $page_courante === 'releve_notes.php' ? 'active' : '' ?>"><i class="fa-solid fa-file-pdf"></i> Relevé Annuel</a>
        <?php endif; ?>
    </nav>

    <div class="logout-link">
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </div>
</div>
