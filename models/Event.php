<?php

class Event
{
    private ?int $id = null;
    private ?Media $media = null;
    private ?Type $type = null;
    private ?Style $style1 = null;
    private ?Style $style2 = null;

    public function __construct(
        private string $name,
        private string $main_description,
        private string $description,
        private DateTime $date,
        private DateTime $debut,
        private DateTime $end,
        private int $ticket_price,
        private ?int $mediaId,
        private ?int $type_id,
        private ?int $style1_id,
        private ?int $style2_id,
        private ?string $video_link,
        private string $ticketing_link
    ) {
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getMainDescription(): string
    {
        return $this->main_description;
    }

    public function setMainDescription(string $main_description): void
    {
        $this->main_description = $main_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDebut(): DateTime
    {
        return $this->debut;
    }

    public function setDebut(DateTime $debut): void
    {
        $this->debut = $debut;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    public function setEnd(DateTime $end): void
    {
        $this->end = $end;
    }

    public function getTicketPrice(): int
    {
        return $this->ticket_price;
    }

    public function setTicketPrice(int $ticket_price): void
    {
        $this->ticket_price = $ticket_price;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): void
    {
        $this->type = $type;
    }

    public function getStyle1(): ?Style
    {
        return $this->style1;
    }

    public function setStyle1(?Style $style1): void
    {
        $this->style1 = $style1;
    }

    public function getStyle2(): ?Style
    {
        return $this->style2;
    }

    public function setStyle2(?Style $style2): void
    {
        $this->style2 = $style2;
    }

    public function getVideoLink(): ?string
    {
        return $this->video_link;
    }

    public function setTicketingLink(string $ticketing_link): void
    {
        $this->ticketing_link = $ticketing_link;
    }


    public function getTicketingLink(): string
    {
        return $this->ticketing_link;
    }
}
