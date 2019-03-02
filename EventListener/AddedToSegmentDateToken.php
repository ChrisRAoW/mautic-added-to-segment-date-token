<?php
namespace MauticPlugin\AddedToSegmentDateTokenBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\CoreBundle\Templating\Helper\DateHelper;

/**
 * Class AddedToSegmentDateToken
 */
class AddedToSegmentDateToken extends CommonSubscriber
{
    protected $dateHelper;

    public function __construct(DateHelper $dateHelper)
    {
        $this->dateHelper = $dateHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            EmailEvents::EMAIL_ON_SEND    => ['onEmailGenerate', 2], // When sending it
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailGenerate', 2], // When displaying it in browser

        );
    }

    public function onEmailGenerate(EmailSendEvent $event)
    {
        $content   = $event->getContent();
        $tokenList = [];

        preg_match_all('/{contact:segment_date_added[^}]*}/misU', $content, $segmentDateTokens);

        if (empty($segmentDateTokens[0])) return null;

        foreach ($segmentDateTokens[0] as $segmentDateTokens) {
            if (isset($tokenList[$segmentDateTokens])) continue;

            preg_match('/format="(.*)"/', $segmentDateTokens, $dateFormatMatch);

            $dateFormat = '%e %B, %Y';

            if (!empty($dateFormatMatch)) {
                $dateFormat = $dateFormatMatch[1];
            }

            $getSegmentAddedDate = $this->getSegmentAddedDate($event, $dateFormat);

            $tokenList[$segmentDateTokens] = $getSegmentAddedDate;
        }

        $event->addTokens($tokenList);
    }

    public function getSegmentAddedDate($event, $format)
    {
        $lead = $event->getLead();

        if (!$lead['id']) return '[Date added to segment (No contact)]';

        $lists = $event->getEmail()->getLists();

        if ($lists->isEmpty()) {
            return '[Date added to segment (No lists)]';
        }

        $leadObject = $this->factory->getEntityManager()->getReference('MauticLeadBundle:ListLead', [
            'lead' => $lead['id'],
            'list' => $lists->first()->getId(),
        ]);

        $language = $this->getEmailLanguage($event->getEmail());
        if (!empty($language)) {
            setlocale(LC_ALL, $language);
        }

        return strftime($format, $leadObject->getDateAdded()->getTimestamp());
    }

    public function getEmailLanguage($email)
    {
        $language = $email->getLanguage();

        if (!empty($language) && strpos($language, '_') === false) {
            $language .= '_' .strtoupper($language);
        }

        return $language;
    }
}
