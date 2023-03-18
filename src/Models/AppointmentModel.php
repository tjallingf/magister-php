<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Models\Model;
    use Router\Router;
    use Router\Config;

    class AppointmentModel extends Model {
        protected ?string $meetingUrl = null;

        protected const MAP = [
            'id'               => 'Id',
            'startsAt'         =>['Start', Model::TYPE_DATETIME],
            'endsAt'           =>['Einde', Model::TYPE_DATETIME],
            'startsAtPeriod'   => 'LesuurVan',
            'endsAtPeriod'     => 'LesuurTot',
            'isAllDay'         =>['DuurtHeleDag', Model::TYPE_BOOL],
            'title'            => 'Omschrijving',
            'teachers'         =>['Docenten', Model::REMAP],
            'disciplines'      =>['Vakken', Model::REMAP],
            'location'         =>['Lokatie', Model::REMAP],
            'status'           =>['Status', Model::REMAP],
            'content'          =>['Inhoud', Model::REMAP],
            'customProperties' =>['Aantekening', Model::REMAP],
            'isFinished'       =>['Afgerond', Model::TYPE_BOOL],
            'meetingUrl'       =>[null, Model::CUSTOM],
            'hasAttachments'   =>['HeeftBijlagen', Model::TYPE_BOOL],
            'attachments'      =>['Bijlagen', Model::REMAP],
            'type'             =>['Type', MODEL::REMAP],
            'infoType'         =>['InfoType', MODEL::REMAP]
        ];

        public function custom__meetingUrl(): string|null {
            return $this->meetingUrl;
        }

        public function remap__attachments(?array $bijlagen = null): array|null {
            if(!isset($bijlagen)) return null;

            return array_map(function($bijlage) {
                return [
                    'id'          => strval($bijlage['Id']),
                    'name'        => $bijlage['Naam'],
                    'contentType' => $bijlage['ContentType'],
                    'size'        => $bijlage['Grootte']
                ];
            }, $bijlagen);
        }

        public function remap__teachers(array $docenten): array {
            return array_map(function($docent) {
                return [
                    'id'   => strval($docent['Id']),
                    'name' => $docent['Naam'],
                    'code' => $docent['Docentcode']
                ];
            }, $docenten);
        }

        public function remap__disciplines(array $vakken): array{
            return array_map(function($vak) {
                return [
                    'id'       => strval($vak['Id']),
                    'name'     => $vak['Naam']
                ];
            }, $vakken);
        }

        public function remap__location(?string $lokatie = ''): string|null {
            $empty_chars = '-\'""';
            return strlen(trim($lokatie, $empty_chars)) === 0 ? null : $lokatie;
        }

        public function remap__customProperties(?string $aantekening): array {
            if(!is_string($aantekening)) return [];
            return @json_decode($aantekening, true) ?? [];
        }


        public function remap__infotype(int $infotype): string|null {
            switch($infotype) {
                case 0:  return null;           // Geen
                case 1:  return 'homework';     // Huiswerk
                case 2:  return 'test';         // Proefwerk
                case 3:  return 'exam';         // Tentamen
                case 4:  return 'writtenExam';  // Schriftelijke overhoring
                case 5:  return 'oralExam';     // Mondelinge overhoring
                case 6:  return 'information';  // Informatie
                case 7:  return 'note';         // Aantekening
                default: return 'unknown';
            }
        }

        public function remap__status(int $status): string {
            switch($status) {
                case 1:  return 'scheduled.automatically'; // Geroosterd automatisch
                case 2:  return 'scheduled.manually';      // Geroosterd handmatig
                case 3:  return 'changed';                 // Gewijzigd
                case 4:  return 'canceled.manually';       // Vervallen handmatig
                case 5:  return 'canceled.automatically';  // Vervallen automatisch
                case 6:  return 'inUse';                   // In gebruik
                case 7:  return 'finished';                // Afgesloten
                case 8:  return 'used';                    // Ingezet
                case 9:  return 'moved';                   // Verplaatst
                case 10: return 'changedAndMoved';         // Gewijzigd en verplaatst
                default: return 'unknown';
            }
        }

        public function remap__type(int $type): string|null {
            switch($type) {
                case 0:   return null;                // Geen
                case 1:   return 'personal';          // Persoonlijk
                case 2:   return 'general';           // Algemeen
                case 3:   return 'schoolWide';        // Schoolbreed
                case 4:   return 'internship';        // Stage
                case 5:   return 'intake';            // Intake
                case 6:   return 'free';              // Roostervrij
                case 7:   return 'kwt';               // Keuzewerktijd
                case 8:   return 'standby';           // Standby
                case 9:   return 'blocked';           // Gebelokkeerd
                case 10:  return 'other';             // Overig
                case 11:  return 'blocked.classroom'; // Geblokkeerde lokatie
                case 12:  return 'blocked.class';     // Geblokkeerde klas
                case 13:  return 'lesson';            // Les
                case 14:  return 'studyHouse';        // Studiehuis
                case 15:  return 'freedStudy';        // Roostervrije studie
                case 16:  return 'schedule';          // Planning
                case 101: return 'measures';          // Maatregelen
                case 102: return 'presentations';     // Presentaties
                case 103: return 'exam_schedule';     // Examenrooster

                default:  return 'unknown';
            }
        }

                
        public function remap__content(?string $inhoud): string|null {
            if(!isset($inhoud)) return null;

            $meeting_link_element_regex = '/<a [^>]+href="[htps:\/ ]+teams\.microsoft\.com\/l[^>]+>[A-z ]+<\/a>/';
            $meeting_link_href_regex     = '/(?<=href=")[^"]+/';
            $meeting_snippet_regex      = '/<a [^>]+href="[htps:\/ ]+(support\.office\.com|teams.microsoft.com\/meetingOptions)[^>]+>[A-z ]+<\/a>/';
            $excess_whitespace_regex    = '/(\\n)*\s{2,}|<p><br><\/p>/';
            $excess_html_regex          = '/<hr>|<p>(<br>|)<\/p>|<br \/>|\\n\s*|\|\s*/';

            // Cleanup the content
            $content = nl2br($inhoud);
            $content = preg_replace($excess_whitespace_regex, '', $content);
            $content = preg_replace($excess_html_regex, '', $content);

            // Get meeting link HTML element from content
            if(!preg_match($meeting_link_element_regex, $content, $meeting_link_elements))
                return $content;

            // Get [href] attribute of the link
            if(!preg_match($meeting_link_href_regex, $meeting_link_elements[0], $meeting_link_hrefs))
                return $content;
            
            // Remove meeting link from content
            $content = preg_replace($meeting_link_element_regex, '', $content);

            // Remove other links in the meeting snippet from the content
            $content = preg_replace($meeting_snippet_regex, '', $content);

            // Remove excess html twice
            $content = preg_replace($excess_html_regex, '', $content);
            $content = preg_replace($excess_html_regex, '', $content);

            // Store meeting url for later reference
            $this->meetingUrl = str_replace(' ', '', $meeting_link_hrefs[0]);

            return empty($content) ? null : $content;
        }

    }