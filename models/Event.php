<?php
class Event
{

    private ? int $id = null;

    public function __construct(private string $name, private string $main_description, private string $description, private DateTime $date, private DateTime $debut, private DateTime $end, private int $ticket_price, private ?int $media_id, private ?int $type_id, private ?int $style1_id, private ?int $style2_id, private ?string $video_link)
    {
        
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

    public function getMediaId(): ?int
    {
        return $this->media_id;
    }

    public function setMediaId(?int $media_id): void
    {
        $this->media_id = $media_id;
    }

    public function getTypeId(): ?int
    {
        return $this->type_id;
    }

    public function setTypeId(?int $type_id): void
    {
        $this->type_id = $type_id;
    }

    public function getStyle1Id(): ?int
    {
        return $this->style1_id;
    }

    public function setStyle1Id(?int $style1_id): void
    {
        $this->style1_id = $style1_id;
    }

    public function getStyle2Id(): ?int
    {
        return $this->style2_id;
    }

    public function setStyle2Id(?int $style2_id): void
    {
        $this->style2_id = $style2_id;
    }

    public function getVideoLink(): ?string
    {
        return $this->video_link;
    }

    public function setVideoLink(?string $video_link): void
    {
        $this->video_link = $video_link;
    }

}