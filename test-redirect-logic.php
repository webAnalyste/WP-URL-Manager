<?php
/**
 * Test de la logique de redirection
 * Simule le comportement sans WordPress
 */

// Simulation de la fonction extract_slug_from_url
function extract_slug_from_url($url, $pattern) {
    $url = trim($url, '/');
    $pattern = trim($pattern, '/');
    
    echo "URL: {$url}\n";
    echo "Pattern: {$pattern}\n";
    
    $regex = preg_replace('/%postname%/', '([^/]+)', $pattern);
    $regex = preg_replace('/%post_id%/', '([0-9]+)', $regex);
    $regex = preg_replace('/%[a-z_]+%/', '[^/]+', $regex);
    $regex = preg_replace('/\{taxonomy:[a-z_]+\}/', '[^/]+', $regex);
    
    $regex = '/^' . str_replace('/', '\/', $regex) . '$/i';
    
    echo "Regex: {$regex}\n";
    
    if (preg_match($regex, $url, $matches)) {
        echo "Matches: " . print_r($matches, true) . "\n";
        return isset($matches[1]) ? $matches[1] : null;
    }
    
    echo "No match\n";
    return null;
}

// Test 1: URL simple
echo "=== TEST 1: URL simple ===\n";
$slug = extract_slug_from_url('test', '%postname%');
echo "Slug extrait: " . ($slug ?? 'NULL') . "\n\n";

// Test 2: URL avec slash
echo "=== TEST 2: URL avec slash ===\n";
$slug = extract_slug_from_url('test/', '%postname%');
echo "Slug extrait: " . ($slug ?? 'NULL') . "\n\n";

// Test 3: URL avec préfixe
echo "=== TEST 3: URL avec préfixe (articles/test) ===\n";
$slug = extract_slug_from_url('articles/test', 'articles/%postname%');
echo "Slug extrait: " . ($slug ?? 'NULL') . "\n\n";

// Test 4: Pattern source simple vs URL simple
echo "=== TEST 4: Pattern /%postname%/ vs URL test ===\n";
$slug = extract_slug_from_url('test', '/%postname%/');
echo "Slug extrait: " . ($slug ?? 'NULL') . "\n\n";

// Test 5: URL complète
echo "=== TEST 5: URL complète avec slug long ===\n";
$slug = extract_slug_from_url('comment-mettre-en-place-la-supervision-humaine-en-ia', '%postname%');
echo "Slug extrait: " . ($slug ?? 'NULL') . "\n\n";

// Test 6: Vérification du problème potentiel
echo "=== TEST 6: Problème potentiel - request_path vs pattern ===\n";
echo "Si request_path = 'test' et source_pattern = '/%postname%/'\n";
echo "Après trim: pattern = '%postname%'\n";
echo "Regex devrait être: /^([^/]+)$/i\n";
$slug = extract_slug_from_url('test', '%postname%');
echo "Slug extrait: " . ($slug ?? 'NULL') . "\n\n";

echo "=== CONCLUSION ===\n";
echo "La fonction extract_slug_from_url() devrait fonctionner correctement.\n";
echo "Le problème peut venir de :\n";
echo "1. \$wp->request qui ne contient pas ce qu'on attend\n";
echo "2. La règle qui n'a pas de source_pattern\n";
echo "3. Le post qui n'est pas trouvé avec get_posts()\n";
