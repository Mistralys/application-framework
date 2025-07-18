<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\ListBuilder;

use Application\TimeTracker\TimeEntry;
use AppUtils\ConvertHelper;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

class TicketSummaryRenderer implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    private string $gridID;

    /**
     * @var TimeEntry[]
     */
    private array $timeEntries;

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function __construct(array $timeEntries)
    {
        $this->timeEntries = $timeEntries;
        $this->gridID = 'ticket_summary_'.nextJSID();
    }

    public function render() : string
    {
        $grid = $this->getUI()->createDataGrid($this->gridID)
            ->setTitle(t('Ticket summary'))
            ->enableCompactMode()
            ->disableFooter();

        $grid->addColumn('ticket', t('Ticket'));
        $grid->addColumn('duration', t('Total duration'));

        return $grid->render($this->collectEntries());
    }

    private function collectEntries() : array
    {
        $entries = array();

        foreach($this->summarizeTickets() as $ticket)
        {
            $duration = $ticket->getDurationString();

            $entries[] = array(
                'ticket' => $ticket->getTicketLinked(),
                'duration' => (string)sb()
                    ->add($duration->getNormalized())
                    ->add('&#160;')
                    ->muted(TimeEntry::duration2hoursDec($duration)),
            );
        }

        return $entries;
    }

    /**
     * @return SummarizedTicket[]
     */
    public function summarizeTickets() : array
    {
        $tickets = array();

        foreach($this->timeEntries as $entry)
        {
            $ticketID = $entry->getTicketID();
            if(empty($ticketID)) {
                continue;
            }

            if(!isset($tickets[$ticketID])) {
                $ticket = new SummarizedTicket($ticketID);
                $tickets[$ticketID] = $ticket;
            } else {
                $ticket = $tickets[$ticketID];
            }

            $ticket->addTimeEntry($entry);

            $url = $entry->getTicketURL();
            if(!empty($url)) {
                $ticket->setTicketURL($url);
            }
        }

        ksort($tickets);

        return array_values($tickets);
    }

    public function setGridID(string $string) : self
    {
        $this->gridID = $string;
        return $this;
    }
}
