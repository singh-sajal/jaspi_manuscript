<?php

namespace App\Http\Controllers\Mail;



use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Http\Client\ResponseSequence;

use function PHPUnit\Framework\isEmpty;

class MailController extends Controller
{
    private $repository;

    public function __construct(MailRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        return $this->repository->index();
    }

    public function getUsersByRole(Request $request)
    {
        return $this->repository->getUsersByRole($request);
    }



    public function sendMail(Request $request)
    {
        return $this->repository->sendMail($request);
    }
}
