<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

// Récupérer les fichiers de l'utilisateur
$stmt = $pdo->prepare("SELECT *, SIZE(encrypted_path) AS file_size, DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') AS formatted_date FROM files WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$files = $stmt->fetchAll();

// Calculer l'espace utilisé
$totalSize = 0;
foreach ($files as $file) {
    $totalSize += isset($file['file_size']) ? $file['file_size'] : 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudStorage - Mes fichiers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .file-list {
            max-height: 60vh;
            overflow-y: auto;
        }
        .file-card {
            transition: transform 0.2s;
        }
        .file-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .search-container {
            position: relative;
        }
        .search-container i {
            position: absolute;
            top: 11px;
            left: 10px;
        }
        .search-container input {
            padding-left: 35px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-cloud-fill me-2"></i>CloudStorage</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['email']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h1 class="mb-0"><i class="bi bi-folder me-2"></i>Mes fichiers</h1>
                <p class="text-muted">Espace utilisé: <?= round($totalSize / (1024*1024), 2) ?> Mo</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="upload.php" class="btn btn-primary btn-lg"><i class="bi bi-cloud-upload me-2"></i>Uploader</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="search-container">
                            <i class="bi bi-search text-muted"></i>
                            <input type="text" id="searchFiles" class="form-control" placeholder="Rechercher un fichier...">
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group">
                            <button type="button" id="sortByName" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-sort-alpha-down me-1"></i>Nom
                            </button>
                            <button type="button" id="sortByDate" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-calendar me-1"></i>Date
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <?php if (count($files) > 0): ?>
                    <div class="file-list">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="filesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Nom</th>
                                        <th scope="col" class="text-center">Taille</th>
                                        <th scope="col" class="text-center">Date</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file): 
                                        $fileExt = pathinfo($file['original_name'], PATHINFO_EXTENSION);
                                        $iconClass = "bi-file";
                                        switch(strtolower($fileExt)) {
                                            case 'pdf': $iconClass = "bi-file-pdf"; break;
                                            case 'jpg': case 'jpeg': case 'png': case 'gif': $iconClass = "bi-file-image"; break;
                                            case 'doc': case 'docx': $iconClass = "bi-file-word"; break;
                                            case 'xls': case 'xlsx': $iconClass = "bi-file-excel"; break;
                                            case 'zip': case 'rar': $iconClass = "bi-file-zip"; break;
                                            case 'mp3': case 'wav': $iconClass = "bi-file-music"; break;
                                            case 'mp4': case 'avi': $iconClass = "bi-file-play"; break;
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <i class="bi <?= $iconClass ?> me-2 text-muted"></i>
                                            <?= htmlspecialchars($file['original_name']) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                                $fileSize = isset($file['file_size']) ? $file['file_size'] : 0;
                                                if ($fileSize < 1024) {
                                                    echo $fileSize . ' o';
                                                } elseif ($fileSize < 1024*1024) {
                                                    echo round($fileSize/1024, 2) . ' Ko';
                                                } else {
                                                    echo round($fileSize/(1024*1024), 2) . ' Mo';
                                                }
                                            ?>
                                        </td>
                                        <td class="text-center"><?= $file['formatted_date'] ?? date('d/m/Y H:i', strtotime($file['created_at'])) ?></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="download.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $file['id'] ?>, '<?= htmlspecialchars($file['original_name']) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-folder-x display-1 text-muted"></i>
                        <p class="h4 mt-3">Aucun fichier trouvé</p>
                        <p class="text-muted">Commencez à uploader des fichiers pour les voir apparaître ici</p>
                        <a href="upload.php" class="btn btn-primary mt-2">
                            <i class="bi bi-cloud-upload me-2"></i>Uploader un fichier
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le fichier <span id="fileName" class="fw-bold"></span> ?</p>
                    <p class="text-danger">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a href="#" id="deleteLink" class="btn btn-danger">Supprimer</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">© <?= date('Y') ?> CloudStorage - Sécurisé avec chiffrement AES-256</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Recherche de fichiers
        document.getElementById('searchFiles').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const filesTable = document.getElementById('filesTable');
            const rows = filesTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const fileName = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                if (fileName.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });

        // Tri des fichiers
        document.getElementById('sortByName').addEventListener('click', function() {
            sortTable(0, true);
        });

        document.getElementById('sortByDate').addEventListener('click', function() {
            sortTable(2, false);
        });

        function sortTable(column, isText) {
            const table = document.getElementById('filesTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            
            rows.sort((a, b) => {
                const aValue = a.getElementsByTagName('td')[column].textContent.trim();
                const bValue = b.getElementsByTagName('td')[column].textContent.trim();
                
                if (isText) {
                    return aValue.localeCompare(bValue);
                } else {
                    // Pour les dates au format DD/MM/YYYY HH:MM
                    return new Date(aValue.split('/').reverse().join('/')) - new Date(bValue.split('/').reverse().join('/'));
                }
            });
            
            rows.forEach(row => tbody.appendChild(row));
        }

        // Confirmation de suppression
        function confirmDelete(fileId, fileName) {
            document.getElementById('fileName').textContent = fileName;
            document.getElementById('deleteLink').href = 'delete.php?id=' + fileId;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>