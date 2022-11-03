<?php

namespace App\Controller;

use App\Entity\AccountTitularityCheck;
use App\Form\AccountTitularityCheckType;
use App\Repository\AccountTitularityCheckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @IsGranted("ROLE_TITULARITY")
 * })
 */
class AccountTitularityCheckController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    private function parseResponseData($xmlString) {
        $root = new Crawler("<?xml version='1.0' encoding='ISO-8859-1'?>".$xmlString);
        $responseArray = [];
        $certCode = $root->filterXPath('.//certCode')->count() > 0 ? $root->filterXPath('.//certCode')->text(): null;
        $alternateAccount = $root->filterXPath('.//updatedHolderCertData')->count() > 0 ? $root->filterXPath('.//updatedHolderCertData/itemToCertNumber')->text(): null;
        $responseArray['certCode'] = $certCode;
        $responseArray['alternateAccount'] = $alternateAccount;
        $aditionalData  = $root->filterXPath('.//aditionalData')->each(function (Crawler $node, $i) {
            $actualNode = null;
            $value = null;
            foreach ($node->children() as $child) {
                if ( $child->nodeName === 'name') {
                    $actualNode = $child->textContent;
                }
                if ( $child->nodeName === 'value') {
                    $value = $child->textContent;
                }
            }
            return [$actualNode => $value];
        });
        foreach($aditionalData as $key => $value) {
            $responseArray = array_merge($responseArray, $value);
        }
        return $responseArray;
    }

    /**
     * @Route("/account/titularity/confirmation", name="app_account_titularity_confirmation")
     */
    public function confirmation(Request $request, EntityManagerInterface $em, AccountTitularityCheckRepository $repo, LoggerInterface $logger): Response
    {
        $response = $request->getContent();
//        $response = urldecode("module=certGateway&holderCertCertifiedData=%3CholderCertCertifiedData%3E%3CholderCertData+accountNumberFormat%3D%27iban%27+encType%3D%27base64%27+type%3D%27accountNumber%27%3E%3CaditionalDataList%3E%3CaditionalData%3E%3Cname%3EerrorExcel%3C%2Fname%3E%3Cvalue%3E%3C%21%5BCDATA%5BLa+EEFF+no+esta+adherida%5D%5D%3E%3C%2Fvalue%3E%3C%2FaditionalData%3E%3CaditionalData%3E%3Cname%3EId%3C%2Fname%3E%3Cvalue%3E%3C%21%5BCDATA%5B6%5D%5D%3E%3C%2Fvalue%3E%3C%2FaditionalData%3E%3C%2FaditionalDataList%3E%3CrequestAdminID%3E04800300002%3C%2FrequestAdminID%3E%3CrequestID%3E048003000020000000000473130522%3C%2FrequestID%3E%3CitemToCertNumber%3E%3C%21%5BCDATA%5BES6402390806780027853522%5D%5D%3E%3C%2FitemToCertNumber%3E%3CcitizenId%3E30659881F%3C%2FcitizenId%3E%3C%2FholderCertData%3E%3CholderCertResponse%3E%3CfinantialOrgCode%3E0239%3C%2FfinantialOrgCode%3E%3CtimeStamp%3E1652424865841%3C%2FtimeStamp%3E%3CcertCode%3E-1000%3C%2FcertCode%3E%3C%2FholderCertResponse%3E%3C%2FholderCertCertifiedData%3E&function=holderCertified");
        $logger->info('Respuesta recibida: '. $response);
        parse_str($response, $params);
        $xmlString = $params['holderCertCertifiedData'];
        $responseArray = $this->parseResponseData($xmlString);
        $accountTitularityCheck = $repo->find($responseArray['Id']);
        $accountTitularityCheck->setResponse($response);
        $accountTitularityCheck->setResponseDate(new \DateTime());
        $accountTitularityCheck->setCertCode($responseArray['certCode']);
        if ($responseArray['certCode'] === AccountTitularityCheck::SUCCESS_TITULAR || $responseArray['certCode'] === AccountTitularityCheck::SUCCESS_AUTHORIZED) {
            $accountTitularityCheck->setAuthorized(true);
            $accountTitularityCheck->setError(false);
        } elseif ( $responseArray['certCode'] === AccountTitularityCheck::SUCCESS_UNAUTHORIZED ) {
            $accountTitularityCheck->setAuthorized(false);
            $accountTitularityCheck->setError(false);
        } else {
            $accountTitularityCheck->setAuthorized(null);
            $accountTitularityCheck->setError(true);
            $accountTitularityCheck->setErrorCode(array_key_exists('errorCode',$responseArray) ? $responseArray['errorCode'] : null);
            $accountTitularityCheck->setErrorMessage(array_key_exists('errorExcel',$responseArray) ? $responseArray['errorExcel'] : null);
            $accountTitularityCheck->setAlternateAccount(array_key_exists('alternateAccount',$responseArray) ? $responseArray['alternateAccount']: null);
        }
        $accountTitularityCheck->setChecked(true);
        $em->persist($accountTitularityCheck);
        $em->flush();
        return new Response('Received');
    }    

    /**
     * @Route("/{_locale}/account/titularity/check", name="app_account_titularity_check", requirements={
     *	    "_locale": "es|eu|en"
     * })
     * @IsGranted("ROLE_USER")
     */
    public function check(Request $request, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $form = $this->createForm(AccountTitularityCheckType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AccountTitularityCheck $data */
            $data = $form->getData();
            $data->setSendDate( new \DateTime());
            $data->setUser($this->getUser());
            $em->persist($data);
            $em->flush();
            return $this->sendRequestToTitularityCheckEndpoint($data, $form, $logger);
        }

        return $this->renderForm('account_titularity_check/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{_locale}/account/titularity", name="app_account_titularity_index", requirements={
     *	    "_locale": "es|eu|en"
     * })
     * @IsGranted("ROLE_USER")
     */
    public function index(AccountTitularityCheckRepository $repo): Response
    {
        $user = $this->getUser();
        $checks = $repo->findBy([
            'user' => $user,
        ],
        ['id' => 'DESC'], $this->getParameter('titularity_check_show_last'));
        return $this->render('account_titularity_check/index.html.twig', [
            'checks' => $checks,
        ]);
    }

    private function sendRequestToTitularityCheckEndpoint(AccountTitularityCheck $data, $form, LoggerInterface $logger): Response {
        try {
            $endpoint = $this->getParameter('titularity_check_endpoint');
            $content = $this->renderView('account_titularity_check/template.xml.twig',[
                'accountTitularityCheck' => $data,
                'requestAdminId' => $this->getParameter('request_admin_id'),
            ]);
            $response = $this->client->request('POST', $endpoint, [
                'headers' => [],
                'body' => 'xmlRPC='.$content,
            ]);
            if ($response->getStatusCode() !== 200) {
                $this->addFlash('error', 'Status code:'. $response->getStatusCode(). ' Content: ' .$response->getContent());
                return $this->renderForm('account_titularity_check/new.html.twig', [
                    'form' => $form,
                ]);
            }
            $logger->info('Request: '. 'xmlRPC='.$content);
            $logger->info('Response: '. $response->getContent());
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->renderForm('account_titularity_check/new.html.twig', [
                'form' => $form,
            ]);
        }
        $this->addFlash('success','messages.successfullySent');
        return $this->redirectToRoute('app_account_titularity_index');        
    }
}
