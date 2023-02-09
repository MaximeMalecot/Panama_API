<?php

namespace App\State;

use App\Dto\FreelancerInfoKYCDto;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\FreelancerInfo;
use Symfony\Component\Security\Core\Security;

final class FreelancerInfoKYCProcessor implements ProcessorInterface
{

    public function __construct(private FreelancerInfoKYCDto $dto, private EntityManagerInterface $em, private Security $security){}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?FreelancerInfo
    {
        if (!$data instanceof FreelancerInfoKYCDto) {
            return null;
        }

        $user = $this->security->getUser();
        if ( $user->getFreelancerInfo()->getIsVerified() ) {
            return null;
        }
        
        $baseUrl = (isset($_ENV['CURL_ENV']) && $_ENV['CURL_ENV'] === "prod" ? "https" : "http") . "://" . (isset($_ENV['HOST']) ? $_ENV['HOST'] : $_SERVER['HTTP_HOST']);

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
        
        curl_exec($ch);
        curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $user->getFreelancerInfo();
    }


}