<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NIKCallbackController extends AbstractController
{

    public function __construct(private readonly LoggerInterface $logger)
    {
        
    }

    // NIK-Question-Response Example:
    // {"threadID":"ed2fdb22-5bf3-4210-ba8b-4f08f55d5920_msg","type":"QST","connID":"715b68bd-0663-4e3e-9122-7a4adf7aacad","returnContext":{"petID":"9d9c66f8-825c-4a64-800a-af5904b9a0df","taxID":"45624643A"}}
    // Luego con el ThreadID vas a buscar la respuesta concreta.
    // {"status":1,"payload":{"type":"qst","answerDate":1728562862,"answer":"otro","encryptedAnswer":"eyJhbGciOiJSU0EiLCJlbmMiOiJBMjU2Q0JDLUhTNTEyIiwia2lkIjoia2V5cy0zIn0.Zx8Yl9aL4N8omh1Zl7V7FmhEycEmLpiQwAGas8RzZ87Ewd9rnweszM4HIwgaQix95Wvddn0t30f8\nzQkZMU6qye8fyamTHqk0di3QVlD2NMQ3OLUkK83rMOW5V0Gufgu-oloFn3DwuNdK1RsESNFroJ6l\nqTf7lEuG0wQnTKbQVIPxMi-Ovc3MPAL2QukOfD2Z9Uo-ptwca11Fgt2PKr8m2BFniG8JPb1p-FiI\nvQKON8QVx4r8UbyB6pxYEjlF_KB5ZZ5HEUFqEBxojMUTgUrfrDP_zAksImnDeJ_AyS2qPSWZwfSP\neJEaI3KeqNgz_JGFvuZA2Fr4hAR1qdBOQdz1VQ\n.1FLE8UpWAnS-VPlMKWGf9A.o1YiHMIdkiDp83mbTkHWEOPB5Gh2lGrEIDWBeOjAna_5Rq9VsBAjCnsafGtepyNvEa0x9x6YjLhIqj0aLtwG-Cn2APHAbYJBO3jW6rMfXj8GLjLO6AOlkxB2do4IaUpjlpuU1rJL1SvW47JqI5qteAARd7emPbdMe1msxPohbV9qYY5FHHjfnX4RBwU2U0q2IeJg08sMhJSgCb2_ycSsmnpu7tTmjA_d_VjKbrlag0rxJ3IzTr46PbkBg1UgtV841ODDLB7Ubxfb47oL72e5mZSkMBbaxlVBy9u_BxCxn3hmIo5P0yGQ0UwZLPCNEp2njA_8SEjrwQ-nrYo_RtaQ4Wso_uxnZfCyWYfa0GZJQQBRk5FrI15WdgVmvCJKL6E2lw-SBafpywEAKi3yiJmigJ3UNNXbbrXx0NpOW_T0iXDw5G_UifjZhDB6CAfyvstPcOVMYsQmnhtA2gIBNk2CUvLn9enmQTAaNrwOoBsfhYZBzFvTtmf3f-9kIsU7Z7TxlLIjdmoXig9KvbWmPtZTw3Q1GsSKF981yYSNOddjwKshKzVNvbiS9Kz3Zw9R1q-xCbdJYwLOd04HrXHExCo71V5mTMs3Y80veOVITc9O6yaFCfsCnYStCankCU_3GvfUxoQ0sz7BUmnZ3neYuBlxjM_Iw3aqmHSqyiwvu2IRwRjalCVf6yM2hid0BsG-SAeS42TuGFhAKhvPslJ53TjHoXFsvlVSx2pcp7olIOrXvsiTFIIXXlAcqxhdf5CV_1psBbp1zP47b1Rcw9xoCHsoQ9G2KMpGPYY4UR-0xYTl7gmv_dPnqP-QN-eTOybp8zUFcmyBkfdd2_ppGZ4nCfBe5-dVRvSGcxY7C33SbvJFwXy0ZyYBUCfprIvwAQUDfCreh8jAjQ-6YcNXHx5Q3fAKbnH9175zeMaq8JD8ra_aI_BbwbumYW4Qk7b3GQjLLis_4XgU2XsA2Sr_K6VplfcfVy4i9g7-pFCd_NiHoS7eiNBZAG9PxmkhceBwavJsm4i6.VJw0ljhmPvyrzgHp6TsueiqNBBOozaGIFES1ikwvs3T6rpxznLJpmy19dbbrqsB01pqwhxY728y1iPJ8L1In7Q"}}
    // Y con el petitionID te muestra el resumen de la petición
    // {"answered":1,"NoPermission":0,"total":1,"result":[{"threadID":"ed2fdb22-5bf3-4210-ba8b-4f08f55d5920","connUUID":"715b68bd-0663-4e3e-9122-7a4adf7aacad","taxID":"45624643A","status":"RESPONDIDO","timestamp":1728562862}]}
    // Methods: POST
    #[Route('/nik-callback', name: 'app_nik_callback', methods:['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $this->logger->info($request->getContent());
        return new Response($request->getContent());
    }
}
