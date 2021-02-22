<?php


namespace App\Service;


use App\Entity\Song;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class SongService
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * SongService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     * @return Song|null
     */
    public function getSong(int $id): Song|null
    {
        return $this->entityManager
            ->getRepository(Song::class)
            ->find($id);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function createSong( array $data): bool
    {
        $song = new Song();
        $song->setName($data['Name']);
        $song->setAuthor($data['Author']);
        $song->setBody($data['Body']);
        $song->setMadeBy($data['MadeBy']);
        if (isset($data['Capo'])) {
            $song->setCapo($data['Capo']);
        }
        if (isset($data['Revision'])) {
            $song->setRevision($data['Revision']);
        }

        $this->entityManager->persist($song);

        $this->entityManager->flush();

        return true;
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateSong(int $id, array $data): bool
    {
        $song = $this->entityManager
            ->getRepository(Song::class)
            ->find($id);

        if (!$song) {
            return false;
        }

        if (isset($data['Name'])) {
            $song->setName($data['Name']);
        }
        if (isset($data['Author'])) {
            $song->setAuthor($data['Author']);
        }
        if (isset($data['Body'])) {
            $song->setBody($data['Body']);
        }
        if (isset($data['Capo'])) {
            $song->setCapo($data['Capo']);
        }
        if (isset($data['Revision'])) {
            $song->setRevision($data['Revision']);
        }

        $this->entityManager->flush();

        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteSong(int $id): bool
    {
        $song = $this->entityManager
            ->getRepository(Song::class)
            ->find($id);

        if (!$song) {
            return false;
        }

        $this->entityManager->remove($song);

        $this->entityManager->flush();
        return true;
    }

    /**
     * @return bool
     */
    public function sync(): bool
    {
        $datetime = new DateTime();
        $data = Yaml::parseFile(dirname(__DIR__) . '/../var/songs.yaml');

        if ((!isset($data['timestamp'])) || $data['timestamp'] == null) {
            $data['timestamp'] = 0;
        }

        $diff = abs($datetime->getTimestamp() - $data['timestamp']) / 60;

        if ($diff >= 15) {
            $list = $this->entityManager
                ->getRepository(Song::class)
                ->getColumns(['id', 'name', 'author']);
            $data['list'] = $list;
            if (file_put_contents(dirname(__DIR__) . '/../var/songs.yaml', Yaml::dump($data))) {
                return true;
            }

            return false;
        }

        return true;

    }

    /**
     * @param string|null $sortBy
     * @return array
     */
    public function getList(?string $sortBy): array
    {
        if ($sortBy === null) {
            $sortBy = 'name';
        }
        $this->sync();
        $data = Yaml::parseFile(dirname(__DIR__) . '/../var/songs.yaml');
        $list = $data['list'];

        foreach ($list as $key => $item) {
            setlocale(LC_CTYPE, 'cs_CZ');
            $name[$key] = iconv('utf-8', 'ascii//TRANSLIT', $item['name']);
            $author[$key] = iconv('utf-8', 'ascii//TRANSLIT', $item['author']);
        }

        if ($sortBy === 'name') {
            array_multisort(
                $name, SORT_STRING, SORT_ASC,
                $author, SORT_STRING, SORT_ASC,
                $list
            );
        } elseif ($sortBy === 'author') {
            array_multisort(
                $author, SORT_STRING, SORT_ASC,
                $name, SORT_STRING, SORT_ASC,
                $list
            );
        }

        return $list;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getAdjacent(int $id): array
    {
        $songs = $this->getList('name');

        foreach ($songs as $position => $song) {
            if ($song['id'] == $id) {
                $currentPos = $position;
                break;
            }
        }

        if ($currentPos == array_key_first($songs)) {
            return array('prev' => -1, 'next' => $songs[$currentPos + 1]['id']);
        } elseif ($currentPos == array_key_last($songs)) {
            return array('prev' => $songs[$currentPos - 1]['id']);
        } else {
            return array('prev' => $songs[$currentPos - 1]['id'], 'next' => $songs[$currentPos + 1]['id']);
        }
    }

    public function getFirst()
    {
        $songs = $this->getList('name');
        return $songs[0]['id'];
    }

    public function getGenders($madeBy, $revision )
    {
        $result= array();
        if (str_contains($madeBy, '{male}')) {
            $result['madeGender'] = '2';
            $made = str_replace(' {male}', '', $madeBy);
            $result['made'] = $made;
        } elseif (str_contains($madeBy, '{female}')) {
            $result['madeGender'] = '1';
            $made = str_replace(' {female}', '', $madeBy);
            $result['made'] = $made;
        }

        $rev = $revision;
        if ($rev != null && sizeof($rev) != 1) {
            $start = $rev[0];
            $onlyFemales = true;
            $makers = '';
            foreach ($rev as $maker) {
                if (str_contains($maker, '{male}')) {
                    $onlyFemales = false;
                }
                if ($start !== $maker) {
                    $maker = str_replace(' {female}', '', $maker);
                    $maker = str_replace(' {male}', '', $maker);
                    $makers .= ', ' . $maker;
                } else {
                    $maker = str_replace(' {female}', '', $maker);
                    $maker = str_replace(' {male}', '', $maker);
                    $makers .= $maker;
                }
            }
            if ($onlyFemales) {
                $result['revision'] = $makers;
                $result['revisionGender'] = '3';
            } else {
                $result['revision'] = $makers;
                $result['revisionGender'] = '4';
            }
        } elseif (($rev != null && sizeof($rev) == 1)) {
            if (str_contains($rev[0], '{male}')) {
                $text = str_replace(' {male}', '', $rev[0]);
                $result['revision'] = $text;
                $result['revisionGender'] = '2';
            } elseif (strpos($rev[0], '{female}') !== false) {
                $text = str_replace(' {female}', '', $rev[0]);
                $result['revision'] = $text;
                $result['revisionGender'] = '1';
            } else {
                $result['revision'] = $rev[0];
            }
        } else {
            $result['revisionGender'] = null;
            $result['revision'] = null;
        }

        return $result;
    }

    /**
     * @param string $song
     * @param string $in possible: 'db', 'html'
     * @param string $out possible: 'db', 'html'
     * @return string
     */
    public function songParser(string $song, string $in, string $out): string
    {
        if ($in === 'html' && $out === 'db') {
            $song = str_replace('<wrapper><chord>', '[!', $song);
            $song = str_replace('</chord></wrapper>', '!]', $song);
            $song = str_replace('<verse number="', '[@', $song);
            $song = str_replace('"></verse>', '@]', $song);
            $song = str_replace('<br>', '\n', $song);
        } elseif ($in === 'db' && $out === 'html') {
            $song = str_replace('[!', '<wrapper><chord>', $song);
            $song = str_replace('!]', '</chord></wrapper>', $song);
            $song = str_replace('[@', '<verse number="', $song);
            $song = str_replace('@]', '"></verse>', $song);
            $song = str_replace('\n', '<br>', $song);
        }

        return $song;
    }
}