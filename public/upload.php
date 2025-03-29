<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Gestion de l'upload du fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Vérification des erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = "Le fichier est trop volumineux.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = "Le fichier n'a été que partiellement téléchargé.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = "Aucun fichier n'a été téléchargé.";
                break;
            default:
                $error = "Une erreur est survenue lors de l'upload.";
        }
    } else {
        // Chiffrement du fichier
        $key = 'ma_cle_secrete_32_caracteres!'; // Clé de 32 caractères pour AES-256
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt(file_get_contents($file['tmp_name']), 'aes-256-cbc', $key, 0, $iv);
        
        // Préparation du dossier de stockage
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid() . '.enc';
        $filePath = $uploadDir . $filename;
        
        // Écriture du fichier chiffré
        if (file_put_contents($filePath, $iv . $encrypted) !== false) {
            $stmt = $pdo->prepare("INSERT INTO files (user_id, original_name, encrypted_path) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $file['name'], $filename]);
            
            // Redirection avec un message de succès
            $_SESSION['success_message'] = "Votre fichier a été chiffré et uploadé avec succès!";
            header("Location: index.php");
            exit;
        } else {
            $error = "Erreur lors de l'upload : impossible d'écrire le fichier.";
        }
    }
}

// Récupération de la taille maximale d'upload autorisée
$max_upload = min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
$max_upload = str_replace('M', ' MB', $max_upload);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Sécurisé | Mon Cloud</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6ee0;
            --primary-hover: #3a5ecc;
            --secondary-color: #e0e0e0;
            --text-color: #333;
            --light-bg: #f9f9f9;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        
        h1 {
            color: var(--primary-color);
            font-size: 24px;
        }
        
        .nav-link {
            display: inline-flex;
            align-items: center;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .nav-link i {
            margin-right: 8px;
        }
        
        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .upload-container {
            border: 2px dashed var(--secondary-color);
            border-radius: var(--border-radius);
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            transition: all 0.3s;
        }
        
        .upload-container:hover, .upload-container.dragover {
            border-color: var(--primary-color);
            background-color: rgba(74, 110, 224, 0.05);
        }
        
        .upload-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .file-label {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            margin-top: 15px;
            transition: background-color 0.3s;
        }
        
        .file-label:hover {
            background-color: var(--primary-hover);
        }
        
        #file-name {
            margin-top: 10px;
            font-weight: 500;
        }
        
        .file-input {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }
        
        .submit-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: var(--primary-hover);
        }
        
        .submit-btn:disabled {
            background-color: var(--secondary-color);
            cursor: not-allowed;
        }
        
        .upload-info {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 20px;
        }
        
        .progress-container {
            width: 100%;
            background-color: var(--secondary-color);
            border-radius: 10px;
            margin: 20px 0;
            display: none;
        }
        
        .progress-bar {
            height: 10px;
            border-radius: 10px;
            background-color: var(--primary-color);
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-cloud-upload-alt"></i> Upload Sécurisé</h1>
            <a href="index.php" class="nav-link">
                <i class="fas fa-arrow-left"></i> Retour à mes fichiers
            </a>
        </header>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="upload-form">
            <div class="upload-container" id="drop-area">
                <div class="upload-icon">
                    <i class="fas fa-file-upload"></i>
                </div>
                <p>Glissez et déposez votre fichier ici ou</p>
                <label for="file" class="file-label">
                    <i class="fas fa-folder-open"></i> Parcourir
                </label>
                <input type="file" name="file" id="file" class="file-input" required>
                <div id="file-name"></div>
            </div>
            
            <div class="progress-container" id="progress-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            
            <button type="submit" class="submit-btn" id="submit-btn" disabled>
                <i class="fas fa-lock"></i> Chiffrer et uploader
            </button>
            
            <p class="upload-info">
                <i class="fas fa-info-circle"></i> Taille maximale: <?php echo $max_upload; ?><br>
                <small>Les fichiers sont chiffrés avant d'être stockés</small>
            </p>
        </form>
    </div>
    
    <script>
        // Sélection des éléments DOM
        const fileInput = document.getElementById('file');
        const fileNameDisplay = document.getElementById('file-name');
        const submitBtn = document.getElementById('submit-btn');
        const dropArea = document.getElementById('drop-area');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const uploadForm = document.getElementById('upload-form');
        
        // Gestion de l'affichage du nom du fichier sélectionné
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / (1024 * 1024)).toFixed(2);
                fileNameDisplay.innerHTML = `<i class="fas fa-file"></i> ${fileName} (${fileSize} MB)`;
                submitBtn.disabled = false;
            } else {
                fileNameDisplay.textContent = '';
                submitBtn.disabled = true;
            }
        });
        
        // Prévention des comportements par défaut pour le drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });
        
        // Ajout/suppression de classe pour le style lors du drag over
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                dropArea.classList.add('dragover');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                dropArea.classList.remove('dragover');
            }, false);
        });
        
        // Gestion du drop de fichier
        dropArea.addEventListener('drop', function(e) {
            fileInput.files = e.dataTransfer.files;
            
            // Déclencher l'événement change manuellement
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }, false);
        
        // Simulation d'une barre de progression (pour UX uniquement)
        uploadForm.addEventListener('submit', function() {
            if (fileInput.files.length > 0) {
                // Afficher la barre de progression
                progressContainer.style.display = 'block';
                submitBtn.disabled = true;
                
                // Simuler la progression
                let width = 0;
                const interval = setInterval(function() {
                    if (width >= 95) {
                        clearInterval(interval);
                    } else {
                        width += Math.random() * 10;
                        progressBar.style.width = Math.min(width, 95) + '%';
                    }
                }, 300);
            }
        });
    </script>
</body>
</html>