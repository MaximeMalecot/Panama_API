<?php

namespace App\State;

use App\Entity\User;
use App\Dto\FreelancerInfoKYCDto;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Security\Core\Security;

final class FreelancerInfoKYCProcessor implements ProcessorInterface
{

    public function __construct(private FreelancerInfoKYCDto $dto, private EntityManagerInterface $em, private Security $security){}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof FreelancerInfoKYCDto) {
            return null;
        }

        $user = $this->security->getUser();
        if ( $user->getFreelancerInfo()->getIsVerified() ) {
            return null;
        }
        if(isset($_ENV['SERVER_DNS'])){
            $baseUrl = "http://".$_ENV['SERVER_DNS'];
        } else {
            $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        }

        dump($user, $baseUrl);

        $uri = array(
            "success_uri" => $baseUrl."/webhook/kyc_verification/{$user->getId()}?status=success",
            "error_uri" => $baseUrl."/webhook/kyc_verification/{$user->getId()}?status=failed",
        );

        $content = array("siret" => $data->siret, "name" => $user->getName(), "surname" => $user->getSurname());
        $body = array("uri" => $uri, "data" => $content);
        $body_string = json_encode($body);
        
        $ch = curl_init($_ENV["KYC_API_URL"]);                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($body_string))                                                                       
        );
        
        $result = curl_exec($ch);
        $httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        dump($result, $ch, $httpReturnCode);

        return null;
    }


}