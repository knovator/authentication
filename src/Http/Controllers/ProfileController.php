<?php

namespace Knovators\Authentication\Http\Controllers;

use Knovators\Authentication\Repository\ProfileRepository;

/**
 * Class ProfileController
 * @package App\Http\Controllers
 */
class ProfileController extends Controller
{

    protected $profileRepository;

    /**
     * ProfileController constructor.
     * @param ProfileRepository $profileRepository
     */
    public function __construct(ProfileRepository $profileRepository) {
        $this->profileRepository = $profileRepository;
    }


    public function show() {

    }


    public function store() {

    }


    public function update() {

    }

    public function index() {


    }

}
