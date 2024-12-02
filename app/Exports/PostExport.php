<?php

namespace App\Exports;

use App\Models\Post;
use App\Models\User;
use App\Models\State;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithMapping;


class PostExport implements FromCollection, WithCustomCsvSettings, WithHeadings, WithColumnFormatting, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {   
        $items = exportItems();
        
        $data = Post::whereBetween('created_at', [$this->from, $this->to])
        ->select($items)
        ->oldest()
        ->get();
        
        foreach ($data as $datum)
        {
            $datum->shop_id = $datum->user->name;
            $state = $datum->state->post_state == 'negative' ? '注意' : null;
            $state = $datum->state->post_active == 'active' ? '要対応' : $state;
            $state = $datum->state->post_ng == 'NG' ? '非公開' : $state;
            $datum->state = $state;
        }
        return $data;
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
        ];
    }

    public function map($row): array
    {
        $date[] = Date::dateTimeToExcel($row->created_at);
        $items = exportItems();
        $res[] = $row->state;
        foreach ($items as $item) {
            $res[] = $row->$item;
        }
        array_splice($res, 1, 1);
        return $res;
    }
    
    public function headings(): array
	{
        $res = csvHeaders();
        return $res;
	}
}