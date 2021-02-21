<?php


namespace App\Controller;


use App\Service\SongService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class PageController extends AbstractController
{
    public function home(): Response
    {
        return $this->render('home.html.twig', [
            'right' => '/list'
        ]);
    }

    public function list(Request $request, SongService $songService): Response
    {
        $sortBy = $request->query->get('sortBy');

        $list = $songService->getList($sortBy);
        $first = $songService->getFirst();

        return $this->render('list.html.twig', [
            'left' => '/',
            'right' => '/song/' . $first,
            'songs' => $list
        ]);
    }

    public function song($id, SongService $songService): Response
    {
        $song = $songService->getSong($id);

        $adjacent = $songService->getAdjacent($id);

        $song_params = $songService->getGenders($song->getMadeBy(), $song->getRevision());

        if (isset($song_params['next'])) {
            return $this->render('song.html.twig', [
                'left' => $adjacent['prev'],
                'right' => $adjacent['next'],
                'name' => $song->getName(),
                'author' => $song->getAuthor(),
                'body' => $song->getBody(),
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
                'body' => $song->getBody(),
                'capo' => $song->getCapo(),
                'made' => $song_params['made'],
                'revision' => $song_params['revision'],
                'made_gender' => $song_params['madeGender'],
                'revision_gender' => $song_params['revisionGender']
            ]);
        }
    }

    public function songCreatorScript(SongService $songService): Response
    {
        $files = scandir(dirname(__DIR__) . '/../local_data/songs/');

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $file_contents = \Spatie\YamlFrontMatter\YamlFrontMatter::parseFile(dirname(__DIR__) . '/../local_data/songs/' . $file);
            $data = array(
                'Name' => $file_contents->matter('title'),
                'Author' => $file_contents->matter('author'),
                'Body' => $file_contents->body(),
                'MadeBy' => $file_contents->matter('made'),
                'Capo' => $file_contents->matter('capo'),
                'Revision' => $file_contents->matter('revision')
            );

            $songService->createSong($data);
        }
        return new Response(
            '<html><body>success</body></html>'
        );
    }
}