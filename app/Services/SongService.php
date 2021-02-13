<?php


namespace App\Services;


class SongService
{
    /**
     * @param string $sortBy
     * @return mixed
     */
    public function getSongList(string $sortBy): mixed
    {
        $data = json_decode(file_get_contents(dirname(__DIR__) . '/../data/songs.json'), true);
        if ($sortBy == null) {
            $sortBy = 'SongName';
        }

        foreach ($data as $key => $row) {
            $SongName[$key]  = $row['SongName'];
            $SongAuthor[$key] = $row['SongAuthor'];
        }

        if ($sortBy === 'SongAuthor'){
            array_multisort($SongAuthor, SORT_STRING, SORT_ASC,
                $SongName, SORT_STRING, SORT_ASC,
                $data);
        } elseif ($sortBy === 'SongName'){
            array_multisort($SongName, SORT_STRING, SORT_ASC,
                $SongAuthor, SORT_STRING, SORT_ASC,
                $data);
        }

        return $data;
    }

    /**
     * @param int $currentID
     * @return string[]
     */
    public function getAdjacentSongs(int $currentID): array
    {
        $songs = $this->getSongList('SongName');
        foreach ($songs as $position => $song){
            if ($song['SongID'] == $currentID) {
                $currentPos = $position;
            }
        }
        if ($currentPos == array_key_first($songs)){
            return array("prev" => "/list", "next" => "?id=" . $songs[$currentPos+1]['SongID']);
        } elseif ($currentPos == array_key_last($songs)) {
            return array("prev" => "?id=" . $songs[$currentPos-1]['SongID']);
        } else {
            return array("prev" => "?id=" . $songs[$currentPos - 1]['SongID'], "next" => "?id=" . $songs[$currentPos + 1]['SongID']);
        }
    }

    /**
     * @param int $ID
     * @return array|null
     */
    public function getSong(int $ID): ?array
    {
        $DS = new DatabaseService();
        $data = $DS->getSong($ID);
        if (str_contains($data['MadeBy'], '{male}')){
            $data['MadeGender'] = '2';
            $made = str_replace(' {male}', '', $data['MadeBy']);
            $data['MadeBy'] = $made;
        } elseif (str_contains($data['MadeBy'], '{female}')){
            $data['MadeGender'] = '1';
            $made = str_replace(' {female}', '', $data['MadeBy']);
            $data['MadeBy'] = $made;
        }

        $revision = json_decode($data['Revision'], true);
        if ($revision != null && sizeof($revision) != 1) {
            $start = $revision[0];
            $onlyFemales = true;
            $makers = '';
            foreach ($revision as $maker) {
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
                $data['Revision'] = $makers;
                $data['RevisionGender'] = '3';
            } else {
                $data['Revision'] = $makers;
                $data['RevisionGender'] = '4';
            }
        } elseif (($revision != null && sizeof($revision) == 1)) {
            if (str_contains($revision[0], '{male}')) {
                $text = str_replace(' {male}', '', $revision[0]);
                $data['Revision'] = $text;
                $data['RevisionGender'] = '2';
            } elseif (strpos($revision[0], '{female}') !== false) {
                $text = str_replace(' {female}', '', $revision[0]);
                $data['Revision'] = $text;
                $data['RevisionGender'] = '1';
            } else {
                $data['Revision'] = $revision[0];
            }
        }

        return $data;
    }
}