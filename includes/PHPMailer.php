<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * Simplified version for SMTP email sending
 */

class PHPMailer {
    public $Host = '';
    public $Port = 587;
    public $SMTPAuth = true;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = 'tls';
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $IsHTML = true;
    public $CharSet = 'UTF-8';
    
    private $to = array();
    private $smtp_connection;
    
    public function addAddress($email, $name = '') {
        $this->to[] = array('email' => $email, 'name' => $name);
    }
    
    public function send() {
        try {
            // Create SMTP connection
            $this->smtp_connection = fsockopen($this->Host, $this->Port, $errno, $errstr, 30);
            
            if (!$this->smtp_connection) {
                throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
            }
            
            // Read server response
            $this->getResponse();
            
            // Send EHLO command
            $this->sendCommand("EHLO " . $_SERVER['HTTP_HOST'] ?? 'localhost');
            
            // Start TLS if required
            if ($this->SMTPSecure === 'tls') {
                $this->sendCommand("STARTTLS");
                stream_socket_enable_crypto($this->smtp_connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand("EHLO " . $_SERVER['HTTP_HOST'] ?? 'localhost');
            }
            
            // Authenticate
            if ($this->SMTPAuth) {
                $this->sendCommand("AUTH LOGIN");
                $this->sendCommand(base64_encode($this->Username));
                $this->sendCommand(base64_encode($this->Password));
            }
            
            // Send mail
            $this->sendCommand("MAIL FROM: <{$this->From}>");
            
            foreach ($this->to as $recipient) {
                $this->sendCommand("RCPT TO: <{$recipient['email']}>");
            }
            
            $this->sendCommand("DATA");
            
            // Build headers
            $headers = "From: {$this->FromName} <{$this->From}>\r\n";
            $headers .= "Reply-To: {$this->From}\r\n";
            $headers .= "Subject: {$this->Subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            
            if ($this->IsHTML) {
                $headers .= "Content-Type: text/html; charset={$this->CharSet}\r\n";
            } else {
                $headers .= "Content-Type: text/plain; charset={$this->CharSet}\r\n";
            }
            
            $headers .= "\r\n";
            
            // Send headers and body
            fwrite($this->smtp_connection, $headers . $this->Body . "\r\n.\r\n");
            $this->getResponse();
            
            // Quit
            $this->sendCommand("QUIT");
            fclose($this->smtp_connection);
            
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendCommand($command) {
        fwrite($this->smtp_connection, $command . "\r\n");
        return $this->getResponse();
    }
    
    private function getResponse() {
        $response = '';
        while (($line = fgets($this->smtp_connection, 515)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
}
?>
