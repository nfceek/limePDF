<?php
// File: src/Security/SecurityManager.php
namespace LimePDF\Security;

class SecurityManager 
{
    private $tcpdf;
    
    public function __construct($tcpdf_instance) 
    {
        $this->tcpdf = $tcpdf_instance;
    }
    

}