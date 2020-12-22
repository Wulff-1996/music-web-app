<?php


namespace Wulff\controllers;


use Wulff\config\Database;
use Wulff\entities\Response;
use Wulff\repositories\SearchRepo;

class SearchController
{
    private string $useCase;
    private Database $db;
    private string $method;
    private SearchRepo $searchRepo;
    private ?string $id;

    public function __construct(string $useCase, string $method, ?int $id)
    {
        $this->useCase = $useCase;
        $this->db = new Database();
        $this->method = $method;
        $this->id = $id;
        $this->searchRepo = new SearchRepo($this->db);
    }

    public function processRequest()
    {
        switch ($this->method) {
            case 'GET':
                $search = isset($_GET['search']) ? $_GET['search'] : null;
                $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
                if (!$page) $page = 0;

                switch ($this->useCase) {

                    case SEARCH_GENRES_PATH:
                        $response = $this->searchGenres($search, $page);
                        break;

                    case SEARCH_MEDIA_PATH:
                        $response = $this->searchMedia($search, $page);
                        break;
                }
                break;

            default:
                $response = Response::notFoundResponse();
                break;
        }

        // send response
        $response->send();

        // close connection
        $this->searchRepo->closeConnection();
    }

    private function searchGenres(string $search, int $page): Response
    {
        $genres = $this->searchRepo->searchGenres($search, $page);
        return Response::success($genres);
    }

    private function searchMedia(string $search, int $page): Response
    {
        $media = $this->searchRepo->searchMedia($search, $page);
        return Response::success($media);
    }

}