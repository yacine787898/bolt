<?php

declare(strict_types=1);

final class AgencyController extends BaseController
{
    private const STORAGE_FILE = __DIR__ . '/../../storage/contact-messages.json';

    public function home(): void
    {
        $flash = $_SESSION['contact_flash'] ?? null;
        unset($_SESSION['contact_flash']);

        $this->render('agency-home', [
            'flash' => $flash,
            'services' => $this->services(),
        ]);
    }

    public function submitContact(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || $email === '' || $message === '') {
            $_SESSION['contact_flash'] = ['type' => 'error', 'text' => 'Veuillez remplir tous les champs.'];
            header('Location: /#contact');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contact_flash'] = ['type' => 'error', 'text' => 'Adresse email invalide.'];
            header('Location: /#contact');
            exit;
        }

        if (mb_strlen($message) > 1000) {
            $_SESSION['contact_flash'] = ['type' => 'error', 'text' => 'Le message dépasse 1000 caractères.'];
            header('Location: /#contact');
            exit;
        }

        $messages = $this->loadMessages();
        $messages[] = [
            'id' => bin2hex(random_bytes(8)),
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'created_at' => date('c'),
        ];

        $this->storeMessages($messages);

        $_SESSION['contact_flash'] = ['type' => 'success', 'text' => 'Merci, votre message a été envoyé.'];
        header('Location: /#contact');
        exit;
    }

    public function adminMessages(): void
    {
        $messages = array_reverse($this->loadMessages());
        $this->render('agency-admin-messages', ['messages' => $messages]);
    }

    /** @return array<int, array<string, string>> */
    private function services(): array
    {
        return [
            [
                'title' => 'Création de page de liens (Biolink)',
                'description' => 'Créez gratuitement votre page bio ultra propre, sans publicités, sur My Meneeto.',
                'icon' => 'fa-link',
                'url' => 'https://my.meneeto.com',
            ],
            [
                'title' => 'Sites web, boutiques & landing pages',
                'description' => 'Conception de sites vitrine, e-commerce et pages de conversion sur mesure.',
                'icon' => 'fa-globe',
                'url' => 'https://wa.me/213660890203',
            ],
            [
                'title' => 'Audio & voix-off Daridja avec IA',
                'description' => 'Générez des voix IA naturelles en Daridja pour vos contenus et publicités.',
                'icon' => 'fa-microphone-lines',
                'url' => 'https://meneeto.com/daridja-ai',
            ],
            [
                'title' => 'Menu numérique pour restaurants',
                'description' => 'Menus digitaux modernes pour restaurants, cafeterias et fast-food.',
                'icon' => 'fa-utensils',
                'url' => 'https://wa.me/213660890203',
            ],
            [
                'title' => 'Produits et services marketing',
                'description' => 'Bientôt disponible : une gamme de solutions marketing prête à l’emploi.',
                'icon' => 'fa-bullhorn',
                'url' => '',
            ],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function loadMessages(): array
    {
        if (!file_exists(self::STORAGE_FILE)) {
            return [];
        }

        $json = file_get_contents(self::STORAGE_FILE);
        if ($json === false || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<int, array<string, string>> $messages */
    private function storeMessages(array $messages): void
    {
        $dir = dirname(self::STORAGE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            self::STORAGE_FILE,
            json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
