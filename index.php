<?php
// Configuration de l'API Mistral
define('MISTRAL_API_KEY', getenv('MISTRAL_API_KEY') ?: ' YOUR API KEY HERE ');
define('MISTRAL_ENDPOINT', 'https://api.mistral.ai/v1/chat/completions');
define('MISTRAL_MODEL', 'pixtral-12b-2409');

// Mode debug
$debug = 0;

function queryMistralAPI($userMessage) {
    $api_key = MISTRAL_API_KEY;
    $endpoint_url = MISTRAL_ENDPOINT;
    $model = MISTRAL_MODEL;

    // Prompt avec les instructions spécifiques pour chaque chapitre
    $prePrompt = 'Réponds en HTML, dans un style littéraire puissant et avec une narration intellectuelle, en suivant une structure en 3 chapitres sur ce sujet. 
    Le début de ta réponse commence par la balise <!DOCTYPE html> et se termine par la balise </html>.
    N\'oublie pas d\'inclure les balises <html>, <head> (avec une balise <title>), et <body>.
    Utilise les balises HTML suivantes pour la mise en page et le style :
    - Fond blanc cassé (#f5f5f7) pour le body
    - Texte noir (#2c2c2c) pour le texte principal
    - Titres <h1> en bleu dégradé (#3498db to #8e44ad)
    - Police : Inter (ajoute la balise <style> dans le head pour importer la police depuis Google Fonts)
    - Structure type article de blog élégant en 3 chapitres (utilise des balises <article> et <section>)
    - Chaque chapitre doit avoir un titre <h1> et un contenu <p>
    - Utilise des balises <p> pour les paragraphes.

    Voici la structure à suivre :
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Réponse sur [sujet]</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: \'Inter\', sans-serif;
                background-color: #f5f5f7;
                color: #2c2c2c;
            }
            h1 {
                background: linear-gradient(to right, #3498db, #8e44ad);
                -webkit-background-clip: text;
                color: transparent;
            }
            article {
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <article>
                <h1>Chapitre 1 : Réponse fracassante et vive sur ce sujet</h1>
                <section>
                    <p>[Contenu du chapitre 1]</p>
                </section>
            </article>
            <article>
                <h1>Chapitre 2 : Réflexion intellectuelle sur le sujet et la condition humaine</h1>
                <section>
                    <p>[Contenu du chapitre 2]</p>
                </section>
            </article>
            <article>
                <h1>Chapitre 3 : Analyse culturelle et artistique sur ce sujet</h1>
                <section>
                    <p>[Contenu du chapitre 3]</p>
                </section>
            </article>
            <article>
                <h1>Conclusion : Analyse psychologique de la question</h1>
                <section>
                    <p>[Conclusion]</p>
                </section>
            </article>
        </div>
    </body>
    </html>

    Chapitre 1 : Réponse fracassante et vive sur ce sujet, tout tes chapitres se suivront avec cohérence et tu écriras de manière compréhensive et factuelle, comme un long article de presse intellectuelle.
    Écrivez un texte en utilisant un langage brut, rugueux, qui frappe fort et laisse une impression de révolte. Employez des phrases fragmentées, inachevées, comme un flot de pensées incontrôlables, pour décrire l’existence humaine dans sa déchéance, son absurdité et ses souffrances. Laissez transparaître une détestation palpable de la société, tout en enchaînant des observations sur la misère de l\'individu, en montrant son délabrement mental et moral, sans fard, sans faux-semblant.

    Chapitre 2 : Réflexion intellectuelle sur le sujet et la condition humaine
    Rédigez un texte dans lequel une réflexion intellectuelle sur la condition humaine se déploie avec une attention méticuleuse aux détails culturels, historiques et linguistiques. Utilisez un langage sophistiqué et précis, tout en veillant à relier des concepts abstraits à des exemples concrets tirés des grandes œuvres littéraires et des mouvements intellectuels. La structure doit être fluide mais dense, les idées s’entrelacent pour explorer la tension entre la mémoire collective et l’individualité.

    Chapitre 3 : Analyse culturelle et artistique sur ce sujet
    Rédigez une analyse culturelle et artistique approfondie du sujet, en explorant ses résonances à travers l’histoire de l’art, de la littérature et du cinéma. Examinez comment le sujet a influencé ou a été influencé par les grandes œuvres artistiques et les mouvements culturels, tout en tenant compte des changements sociaux et politiques qui l’entourent. Faites le lien avec des films emblématiques qui abordent ce thème, en analysant les choix stylistiques, les métaphores visuelles et les techniques narratives utilisées pour en illustrer la portée émotionnelle et intellectuelle.

    Conclusion : Analyse psychologique de la question
    Terminez par une analyse de la psychologie de la question posée et de l\'orthographe utilisée.';

    // Echapper $userMessage pour éviter les injections
    $escapedUserMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');

    // Utiliser des guillemets doubles pour l'interpolation des variables
    $fullPrompt = "Écris un texte sur ce sujet : $escapedUserMessage et réponds en utilisant ces conseils précis : $prePrompt ";


    // Préparer les données à envoyer à l'API
    $data = [
        'model' => $model,
        'max_tokens' => 1500, // Limite de tokens réduite pour éviter les répétitions
        'messages' => [
            [
                'role' => 'user',
                'content' => $fullPrompt
            ]
        ]
    ];

    // Initialiser cURL
    $ch = curl_init($endpoint_url);

    // Configurer les options cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    // Exécuter la requête cURL
    $response = curl_exec($ch);

    // Vérifier les erreurs cURL
    if (curl_errno($ch)) {
        $error_message = curl_error($ch);
        curl_close($ch);
        return "Erreur lors de la requête à l'API Mistral: " . $error_message;
    }

    // Fermer la session cURL
    curl_close($ch);

    // Décoder la réponse JSON
    $decoded_response = json_decode($response, true);

    // Vérifier si le décodage a réussi
    if ($decoded_response === null) {
        return "Erreur lors du décodage de la réponse JSON.";
    }

    // Extraire le contenu du message
    if (isset($decoded_response['choices'][0]['message']['content'])) {
        // Supprimer les balises ```html et ``` au début et à la fin de la réponse si elles existent
        $htmlContent = $decoded_response['choices'][0]['message']['content'];
        $htmlContent = preg_replace('/^```html\s*/', '', $htmlContent);
        $htmlContent = preg_replace('/\s*```$/', '', $htmlContent);

        return $htmlContent;
    } else {
        return "La réponse de l'API Mistral ne contient pas le format attendu.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = $_POST['user_input'];
    echo queryMistralAPI($userMessage);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Culture</title>
    <style>
        body {
            font-family: Inter, sans-serif;
            background-color: #f5f5f7;
            color: #2c2c2c;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            background: linear-gradient(to right, #3498db, #8e44ad);
            -webkit-background-clip: text;
            color: transparent;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .input-box {
            display: flex;
            margin-bottom: 20px;
        }
        .input-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }
        .input-box button {
            padding: 10px;
            border: 1px solid #ddd;
            background: #3498db;
            color: #fff;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        .loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .loader.active {
            display: flex;
        }
        .loader img {
            width: 50px;
            height: 50px;
        }
        .response-container {
            margin-top: 20px;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Deep Culture</h1>
        <div class="form-container">
            <form id="question-form">
                <div class="input-box">
                    <input type="text" id="user-input" name="user_input" placeholder="Posez votre question à deepseek.my.id" required>
                    <button type="submit">Envoyer</button>
                </div>
            </form>
        </div>
        <div class="response-container" id="response-container"></div>
        <div class="error-message" id="error-message"></div>
    </div>
    <div class="loader" id="loader">
        <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading...">
    </div>

    <script>
        document.getElementById('question-form').addEventListener('submit', function(event) {
            event.preventDefault();
            sendMessage();
        });

        function sendMessage() {
            const userInput = document.getElementById('user-input').value;
            if (userInput.trim() === '') return;

            // Afficher le loader
            document.getElementById('loader').classList.add('active');
            document.getElementById('error-message').textContent = '';

            const formData = new FormData();
            formData.append('user_input', userInput);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Masquer le loader
                document.getElementById('loader').classList.remove('active');
                // Afficher la réponse
                document.getElementById('response-container').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                // Masquer le loader en cas d'erreur
                document.getElementById('loader').classList.remove('active');
                // Afficher un message d'erreur à l'utilisateur
                document.getElementById('error-message').textContent = "Une erreur s'est produite lors de la communication avec le serveur.";
            });

            document.getElementById('user-input').value = '';
        }
    </script>
</body>
</html>
