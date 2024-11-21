
<?php
class OpenAIChatAPI {
    private $apiSecretKey;
    private $apiEndpoint = 'https://api.openai.com/v1/chat/completions';

    // Constructor to initialize the API secret key
    public function __construct($apiSecretKey) {
        $this->apiSecretKey = $apiSecretKey;
    }

    // Function to send a rewrite request
    public function rewriteContent($content, $tone = 'neutral') {
        // Create the headers for the request
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiSecretKey
        ];

        // Create the payload for the request
        $data = [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => "You are an assistant that rewrites text in a {$tone} tone."],
                ['role' => 'user', 'content' => "Please rewrite the following text:\n\n{$content}"]
            ],
            'temperature' => 0.7
        ];

        // Use cURL to send the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return 'Error: ' . curl_error($ch);
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the response
        $responseData = json_decode($response, true);

        // Handle API response
        if (isset($responseData['choices'][0]['message']['content'])) {
            return $responseData['choices'][0]['message']['content'];
        } else {
            $errorMessage = $responseData['error']['message'] ?? 'Unknown error occurred.';
            return "API Error: " . $errorMessage;
        }
    }
}

// Callback function for Content Rewrite page
function content_rewrite_page() {
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['chatgpt_api_secret_key'])){
        $chatgpt_api_secret_key = sanitize_text_field( $_POST['chatgpt_api_secret_key'] );

        // Save the API secret key to the database or handle it as needed
        update_option('chatgpt_api_secret_key', $chatgpt_api_secret_key);

        echo '<div style="color: green;">API Secret Key saved successfully!</div>';
    }

    // Retrieve the saved API secret key (if any)
    $saved_key = get_option('chatgpt_api_secret_key', '');

    // Display the page content with the input form
    echo '<h1>Content Rewrite Page</h1> <br>';
    echo '<form method="POST">';
    echo '<label for="api_secret_key">Enter API Secret Key:</label><br>';
    echo "<div style = 'display: flex'>";
    echo '<input type="text" id="api_secret_key" placeholder="Enter Your ChatGpt API Key" name="chatgpt_api_secret_key" value="' . esc_attr($saved_key) . '" style="width: 300px;"><br><br>';
    echo '<button type="submit" style="padding: 10px 20px; background: #007bff; color: #fff; border: 1px solid #007bff; text-transform: uppercase;">Submit</button>';
    echo '</div>';
    echo '</form>';

    // Example usage
    $apiKey = get_option('chatgpt_api_secret_key');

    // Instantiate the class
    $openAI = new OpenAIChatAPI($apiKey);

    // Define content and tone
    $originalContent = "<p>The life of a farmer revolves around the cycles of nature. <strong>They rise with the sun</strong>,<br> <strong>set with the moon</strong>, tending to their fields and livestock with care and dedication.</p>";
    $tone = 'formal';

    // Call the function
    $rewrittenContent = $openAI->rewriteContent($originalContent, $tone);

    // Output the rewritten content
    echo "Rewritten Content:\n" . $rewrittenContent;
}




?>

