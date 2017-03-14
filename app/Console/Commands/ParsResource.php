<?php

namespace App\Console\Commands;

use App\Model\WorkPlace;
use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use \App\Model\Identity;

class ParsResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pars:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //
        $file = file_get_contents('./storage/app/parsresource/Ведущий-инженер, Менеджер проектов.htm');
        //$file = file_get_contents('./storage/app/parsresource/Ведущий-инженер, Менеджер проектов.htm');
        $crawler = new Crawler($file);

        //думал это User id, регулярка по скрипту ищит requestID
        /*$scripts = $crawler->filter('script');
        foreach ($scripts as $key=>$value){
            $script = $value->textContent;
            if(!empty($script)){
                preg_match_all(
                //"requestId\: ' ([\w]+) ',",
                    "/requestId: '(.*)',/",
                    $script, $requestId);
                if(!empty($requestId[1])) {
                    $userId = $requestId[1][0];
                }
            }
        }*/
        //var_dump($idUser);

        $identity = new \stdClass;
        $userId = $crawler->filter('div[data-hh-resume-hash]')->attr('data-hh-resume-hash');
        var_dump($userId);
        $identity->resume_id = $userId;
        //echo $userId;
        /**
         * Главный блок.
         */
        $fio = $crawler->filter('div > .resume-header-name')->text();
        $identity->fio = $fio;

        // TODO birthday
        $blockResum = $crawler->filter('.resume-header-block > p')->text();

        //Пол
        $gender = $crawler->filter('.resume-header-block span[itemprop="gender"] ')->text();//||$crawler->filter('.resume-header-block span[data-qa="resume-personal-gender"]');//   ->text();
        $identity->gender = $gender;
        //Возрост
        $age = $crawler->filter('.resume-header-block span[data-qa="resume-personal-age"]')->text();
        $identity->age = (int)$age;
        /*  TODO: вытащить датут.
        $birthDate = $crawler->filter('.resume-header-block > meta')->attr('content')->;
        $birthDate = $blockResum->filter('meta')->attr('content')->extract('_text');
        var_dump($birthDate);
        var_dump($blockResum->filter('meta')->attr('content'));
        */

        //echo $blockResum . "\n";

        $updateResume = $crawler->filter('div .resume-header-print-update-date')->text();
        $identity->date_update_resume = $updateResume;
        //картинка резюме
        $imgResum = $crawler->filter('img.resume-photo__image')->attr('src');
        //var_dump(urldecode($imgResum));
        $identity->image = urldecode($imgResum);

        $adressInfo = $crawler->filter('.resume-header-block span[itemprop="address"]')->text();
        $addressLocality = $crawler->filter('.resume-header-block span[itemprop="addressLocality"]')->text();
        //||$crawler->filter('.resume-header-block span[data-qa="resume-personal-address"]');
        $personalMetro = $crawler->filter('.resume-header-block span[data-qa="resume-personal-metro"]');
        if ($personalMetro->count()) {
            $personalMetro = $crawler->filter('.resume-header-block span[data-qa="resume-personal-metro"]')->text();
            $identity->metro =  $personalMetro;
        }
        //$personalMetro = $crawler->filter('.resume-header-block span[data-qa="resume-personal-metro"]')->text();
        $identity->city =  $addressLocality;
        //var_dump($adressInfo);

        /**
         * Контактная информация
         */
        $contactPoints = trim($crawler->filter('div[itemprop="contactPoints"]')->text());
        $phone = trim($crawler->filter('span[itemprop="telephone"]')->text());
        //echo $phone;
        $identity->phone = $phone;
        $email = $crawler->filter('a[itemprop="email"]')->text();
        $identity->email  =  $email;
        //var_dump($contactPoints);

        /**
         * Желаемая должность и зарплата
         */
        $position = $crawler->filter('.resume-block__title-text ');
        $position2 = $crawler->filter('span[data-qa="resume-block-title-position"]');
        if ($position->count()) {
            //
            $identity->position = $position->text();

        } elseif($position2->count()) {
            $identity->position = $position2->text();
        }

        $salary1 = $crawler->filter('.resume-block__salary');
        $salary2 = $crawler->filter('span[data-qa="resume-block-salary"]');
        if ($salary1->count()) {
            $identity->salary = $salary1->text();
        } elseif ($salary2->count()) {
            $identity->salary = $salary2->text();
        }

        /**
         * Опыт работы.
         */
        $experience = $crawler->filter('.resume-block__title-text_sub')->text();
        echo "Опыт работы: " . $experience . "\n";
        $identity->experience = $experience;




        //$identity->
        $identityCollect = collect($identity)->toArray();
        $identityInstance = Identity::updateOrCreate($identityCollect);
        $id = $identityInstance->id;

        /**
         * Места работы
         */
        $listWork = $crawler->filter('.resume-block-item-gap');
        if ($listWork->count()){
            foreach ($listWork as $list) {
                $nodeList = new Crawler($list);
                $placeWork = $nodeList->filter('.bloko-columns-row');
                if($placeWork->count()) {
                    $placeWorkArr[] = [
                        'identity_id'=>$id,
                        'value' => $placeWork->text()
                    ];
                }
            }
        }
        foreach ($placeWorkArr as $row){
            WorkPlace::updateOrCreate($row);
        }
        echo "Все успешно записано в БД";
    }
}
