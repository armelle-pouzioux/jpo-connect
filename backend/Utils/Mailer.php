<?php
namespace Utils;
class Mailer {
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $useSmtp;

    public function __construct() {
        // Configuration email - à adapter selon votre hébergement
        $this->fromEmail = 'noreply@laplateforme.io'; // À changer
        $this->fromName = 'JPO Connect - La Plateforme';
        
        // Configuration SMTP (optionnel, sinon utilise mail() de PHP)
        $this->useSmtp = false; // Mettre à true si vous voulez utiliser SMTP
        $this->smtpHost = 'smtp.gmail.com';
        $this->smtpPort = 587;
        $this->smtpUsername = '';
        $this->smtpPassword = '';
    }

    /**
     * Envoie un email de confirmation d'inscription
     */
    public function sendRegistrationConfirmation($userEmail, $jpo) {
        $subject = "Confirmation d'inscription - JPO " . $jpo['place'];
        
        $message = $this->getEmailTemplate('registration_confirmation', [
            'jpo' => $jpo,
            'user_email' => $userEmail
        ]);
        
        return $this->sendEmail($userEmail, $subject, $message);
    }

    /**
     * Envoie un email de rappel
     */
    public function sendReminderEmail($userEmail, $jpo) {
        $subject = "Rappel - JPO " . $jpo['place'] . " demain";
        
        $message = $this->getEmailTemplate('reminder', [
            'jpo' => $jpo,
            'user_email' => $userEmail
        ]);
        
        return $this->sendEmail($userEmail, $subject, $message);
    }

    /**
     * Envoie un email d'annulation de JPO
     */
    public function sendJpoCancelationEmail($userEmail, $jpo) {
        $subject = "Annulation - JPO " . $jpo['place'];
        
        $message = $this->getEmailTemplate('jpo_cancelation', [
            'jpo' => $jpo,
            'user_email' => $userEmail
        ]);
        
        return $this->sendEmail($userEmail, $subject, $message);
    }

    /**
     * Méthode principale d'envoi d'email
     */
    private function sendEmail($to, $subject, $message) {
        if ($this->useSmtp) {
            return $this->sendWithSmtp($to, $subject, $message);
        } else {
            return $this->sendWithPhpMail($to, $subject, $message);
        }
    }

    /**
     * Envoi avec la fonction mail() de PHP
     */
    private function sendWithPhpMail($to, $subject, $message) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headersString = implode("\r\n", $headers);
        
        return mail($to, $subject, $message, $headersString);
    }

    /**
     * Envoi avec SMTP (nécessite une bibliothèque comme PHPMailer)
     */
    private function sendWithSmtp($to, $subject, $message) {
        // Cette méthode nécessiterait PHPMailer ou une autre bibliothèque SMTP
        // Pour l'instant, on utilise mail() comme fallback
        return $this->sendWithPhpMail($to, $subject, $message);
    }

    /**
     * Génère le contenu HTML de l'email selon le template
     */
    private function getEmailTemplate($template, $data) {
        switch ($template) {
            case 'registration_confirmation':
                return $this->getRegistrationConfirmationTemplate($data);
                
            case 'reminder':
                return $this->getReminderTemplate($data);
                
            case 'jpo_cancelation':
                return $this->getJpoCancelationTemplate($data);
                
            default:
                return $this->getDefaultTemplate($data);
        }
    }

    /**
     * Template de confirmation d'inscription
     */
    private function getRegistrationConfirmationTemplate($data) {
        $jpo = $data['jpo'];
        $jpoDate = date('d/m/Y à H:i', strtotime($jpo['date_jpo']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmation d'inscription</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Confirmation d'inscription</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour,</h2>
                    <p>Votre inscription à la Journée Portes Ouvertes a bien été prise en compte !</p>
                    
                    <h3>Détails de votre JPO :</h3>
                    <ul>
                        <li><strong>Lieu :</strong> {$jpo['place']}</li>
                        <li><strong>Date :</strong> {$jpoDate}</li>
                        <li><strong>Description :</strong> " . ($jpo['description'] ?: 'Aucune description') . "</li>
                    </ul>
                    
                    <p>Nous vous enverrons un rappel la veille de l'événement.</p>
                    
                    <p>À bientôt !</p>
                </div>
                <div class='footer'>
                    <p>JPO Connect - La Plateforme<br>
                    Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Template de rappel
     */
    private function getReminderTemplate($data) {
        $jpo = $data['jpo'];
        $jpoDate = date('d/m/Y à H:i', strtotime($jpo['date_jpo']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Rappel JPO</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #ffc107; color: #212529; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #fff3cd; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .highlight { background-color: #fff; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔔 Rappel - JPO demain !</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour,</h2>
                    <p>Nous vous rappelons que vous êtes inscrit(e) à la Journée Portes Ouvertes qui aura lieu <strong>demain</strong> !</p>
                    
                    <div class='highlight'>
                        <h3>Détails de votre JPO :</h3>
                        <ul>
                            <li><strong>Lieu :</strong> {$jpo['place']}</li>
                            <li><strong>Date :</strong> {$jpoDate}</li>
                            <li><strong>Description :</strong> " . ($jpo['description'] ?: 'Aucune description') . "</li>
                        </ul>
                    </div>
                    
                    <p>N'oubliez pas de vous présenter à l'heure ! Nous avons hâte de vous rencontrer.</p>
                    
                    <p>À demain !</p>
                </div>
                <div class='footer'>
                    <p>JPO Connect - La Plateforme<br>
                    Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Template d'annulation de JPO
     */
    private function getJpoCancelationTemplate($data) {
        $jpo = $data['jpo'];
        $jpoDate = date('d/m/Y à H:i', strtotime($jpo['date_jpo']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Annulation JPO</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8d7da; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .alert { background-color: #fff; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>❌ Annulation de JPO</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour,</h2>
                    <p>Nous sommes désolés de vous informer que la Journée Portes Ouvertes à laquelle vous étiez inscrit(e) a été <strong>annulée</strong>.</p>
                    
                    <div class='alert'>
                        <h3>JPO annulée :</h3>
                        <ul>
                            <li><strong>Lieu :</strong> {$jpo['place']}</li>
                            <li><strong>Date :</strong> {$jpoDate}</li>
                            <li><strong>Description :</strong> " . ($jpo['description'] ?: 'Aucune description') . "</li>
                        </ul>
                    </div>
                    
                    <p>Nous vous invitons à consulter notre site web pour découvrir les prochaines dates disponibles.</p>
                    
                    <p>Nous nous excusons pour ce désagrément et espérons vous voir bientôt lors d'une prochaine JPO.</p>
                    
                    <p>Cordialement,<br>L'équipe La Plateforme</p>
                </div>
                <div class='footer'>
                    <p>JPO Connect - La Plateforme<br>
                    Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Template par défaut
     */
    private function getDefaultTemplate($data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>JPO Connect</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>JPO Connect</h1>
                </div>
                <div class='content'>
                    <p>Bonjour,</p>
                    <p>Vous recevez cet email de la part de JPO Connect - La Plateforme.</p>
                </div>
                <div class='footer'>
                    <p>JPO Connect - La Plateforme<br>
                    Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Teste l'envoi d'email
     */
    public function testEmail($to) {
        $subject = "Test - JPO Connect";
        $message = $this->getDefaultTemplate([]);
        
        return $this->sendEmail($to, $subject, $message);
    }
}