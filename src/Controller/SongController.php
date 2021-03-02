<?php

namespace App\Controller;

use App\Service\SongService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property SongService songService
 */
class SongController extends AbstractController
{
    public function __construct(SongService $songService)
    {
        $this->songService = $songService;
    }

    public function list(Request $request): Response
    {
        $sortBy = $request->query->get('sortBy');

        $list = $this->songService->getList($sortBy);
        $first = $this->songService->getFirst();

        return $this->render('list.html.twig', [
            'left' => '/',
            'right' => '/song/' . $first,
            'songs' => $list
        ]);
    }

    public function song($id): Response
    {
        $song = $this->songService->getSong($id);

        $adjacent = $this->songService->getAdjacent($id);

        $song_params = $this->songService->getGenders($song->getMadeBy(), $song->getRevision());

        $body = $this->songService->songParser($song->getBody(), 'db', 'html');

        if ($adjacent['prev'] === -1 && isset($adjacent['next'])) {
            return $this->render('song.html.twig', [
                'left' => '/list',
                'right' => $adjacent['next'],
                'name' => $song->getName(),
                'author' => $song->getAuthor(),
                'body' => $body,
                'capo' => $song->getCapo(),
                'made' => $song_params['made'],
                'revision' => $song_params['revision'],
                'made_gender' => $song_params['madeGender'],
                'revision_gender' => $song_params['revisionGender']
            ]);
        } elseif (isset($adjacent['next'])) {
            return $this->render('song.html.twig', [
                'left' => $adjacent['prev'],
                'right' => $adjacent['next'],
                'name' => $song->getName(),
                'author' => $song->getAuthor(),
                'body' => $body,
                'capo' => $song->getCapo(),
                'made' => $song_params['made'],
                'revision' => $song_params['revision'],
                'made_gender' => $song_params['madeGender'],
                'revision_gender' => $song_params['revisionGender']
            ]);
        } else {
            return $this->render('song.html.twig', [
                'left' => $adjacent['prev'],
                'name' => $song->getName(),
                'author' => $song->getAuthor(),
                'body' => $body,
                'capo' => $song->getCapo(),
                'made' => $song_params['made'],
                'revision' => $song_params['revision'],
                'made_gender' => $song_params['madeGender'],
                'revision_gender' => $song_params['revisionGender']
            ]);
        }
    }

    public function songCreatorScript(): Response
    {
        $files = scandir(dirname(__DIR__) . '/../local_data/songs/');

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $file_contents = \Spatie\YamlFrontMatter\YamlFrontMatter::parseFile(dirname(__DIR__) . '/../local_data/songs/' . $file);
            $body = $this->songService->songParser($file_contents->body(), 'html', 'db');
            $data = array(
                'Name' => $file_contents->matter('title'),
                'Author' => $file_contents->matter('author'),
                'Body' => $body,
                'MadeBy' => $file_contents->matter('made'),
                'Capo' => $file_contents->matter('capo'),
                'Revision' => $file_contents->matter('revision')
            );

            $this->songService->createSong($data);
        }
        return new Response(
            '<html><body>success</body></html>'
        );
    }
}
