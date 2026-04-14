<?php
require_once 'config.php';

echo "<h2>Diagnostic de la Base de Données</h2>";

try {
    // 1. Vérifier la table etudiants
    $etudiants_cols = $pdo->query("DESCRIBE etudiants")->fetchAll(PDO::FETCH_COLUMN);
    echo "Table 'etudiants' : ";
    if (!in_array('section', $etudiants_cols)) {
        $pdo->exec("ALTER TABLE etudiants ADD COLUMN section VARCHAR(1) DEFAULT 'A' AFTER niveau");
        echo "<span style='color:green;'>Colonne 'section' ajoutée avec succès !</span><br>";
    } else {
        echo "<span style='color:blue;'>Déjà à jour.</span><br>";
    }

    // 2. Vérifier la table modules
    $modules_cols = $pdo->query("DESCRIBE modules")->fetchAll(PDO::FETCH_COLUMN);
    echo "Table 'modules' : ";
    $added = [];
    if (!in_array('credits', $modules_cols)) {
        $pdo->exec("ALTER TABLE modules ADD COLUMN credits INT DEFAULT 3 AFTER coefficient");
        $added[] = "credits";
    }
    if (!in_array('semestre', $modules_cols)) {
        $pdo->exec("ALTER TABLE modules ADD COLUMN semestre INT DEFAULT 1 AFTER credits");
        $added[] = "semestre";
    }
    if (!in_array('section', $modules_cols)) {
        $pdo->exec("ALTER TABLE modules ADD COLUMN section VARCHAR(1) DEFAULT 'A' AFTER semestre");
        $added[] = "section";
    }

    if (!empty($added)) {
        echo "<span style='color:green;'>Colonnes (".implode(', ', $added).") ajoutées avec succès !</span><br>";
    } else {
        echo "<span style='color:blue;'>Déjà à jour.</span><br>";
    }

    echo "<br><strong style='color:green;'>Tout est prêt ! Vous pouvez maintenant utiliser toutes les actions sans erreur.</strong>";
    echo "<br><br><a href='dashboard_admin.php'>Retour au Dashboard</a>";

} catch (Exception $e) {
    echo "<span style='color:red;'>Erreur lors de la mise à jour : " . $e->getMessage() . "</span>";
}
?>
