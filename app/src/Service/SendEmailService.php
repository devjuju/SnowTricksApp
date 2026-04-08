<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Service centralisé d'envoi d'emails
 *
 * Objectif :
 * - simplifier l'envoi d'emails dans toute l'application
 * - standardiser l'utilisation des templates Twig
 *
 * Permet d'envoyer des emails HTML basés sur des templates Twig
 */
class SendEmailService
{
    public function __construct(private MailerInterface $mailer) {}

    /**
     * Envoie un email basé sur un template Twig
     *
     * @param string $from     Adresse expéditeur
     * @param string $to       Adresse destinataire
     * @param string $subject  Sujet de l'email
     * @param string $template Nom du template (sans extension)
     * @param array  $context  Données passées au template Twig
     */
    public function send(
        string $from,
        string $to,
        string $subject,
        string $template,
        array $context
    ): void {
        // Construction de l'email basé sur un template Twig
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)

            // Template HTML utilisé pour le rendu de l'email
            ->htmlTemplate("emails/$template.html.twig")

            // Variables disponibles dans le template Twig
            ->context($context);

        // Envoi via le Mailer Symfony
        $this->mailer->send($email);
    }
}
