<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    private $sid;
    private $token;
    private $from;
    private $client;

    public function __construct()
    {
        $this->sid = env('TWILIO_SID', '');
        $this->token = env('TWILIO_TOKEN', '');
        $this->from = env('TWILIO_FROM', '');

        if (!empty($this->sid) && !empty($this->token)) {
            try {
                $this->client = new Client($this->sid, $this->token);
            } catch (\Exception $e) {
                Log::error('Error al inicializar Twilio: ' . $e->getMessage());
                $this->client = null;
            }
        }
    }

    /**
     * Enviar un SMS a un número de teléfono
     *
     * @param string $to Número de teléfono destino
     * @param string $message Mensaje a enviar
     * @return array Resultado del envío
     */
    public function sendSMS($to, $message)
    {
        if (!$this->client || empty($this->from)) {
            Log::error('Twilio no configurado correctamente');
            return [
                'success' => false,
                'message' => 'Servicio de SMS no configurado correctamente'
            ];
        }

        try {
            // Formatear el número de teléfono si es necesario
            if (!preg_match('/^\+/', $to)) {
                // Si no comienza con +, agregar +1 (para EE.UU.)
                $to = '+1' . preg_replace('/[^0-9]/', '', $to);
            }

            // Enviar el SMS
            $sms = $this->client->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'body' => $message
                ]
            );

            Log::info('SMS enviado correctamente: ' . $sms->sid);
            return [
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'sid' => $sms->sid
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar SMS: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si el servicio está configurado correctamente
     *
     * @return bool
     */
    public function isConfigured()
    {
        return !empty($this->sid) && !empty($this->token) && !empty($this->from) && $this->client !== null;
    }
}
