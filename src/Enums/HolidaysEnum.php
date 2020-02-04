<?php


namespace CRHolidays\Enums;

use MyCLabs\Enum\Enum;

class HolidaysEnum extends Enum
{
    //Enero
    const ENERO1_NEW_YEAR                                       = 1;
    //Febrero
    const FEBRERO14_DIA_DE_SAN_VALENTIN                         = 2;
    //MARZO
    const MARZO20_DIA_DE_BATALLA_SANTA_ROSA                     = 3;
    //ABRIL
    const ABRIL11_DIA_DE_BATALLA_DE_RIVAS                       = 4;
    //MAYO
    const MAYO1_DIA_DEL_TRABAJADOR                              = 5;
    //JUNIO
    const JUNIO5_DIA_DEL_MEDIO_AMBIENTE                         = 6;
    const JUNIO21_DIA_DEL_PADRE                                 = 7;
    //JULIO
    const JULIO25_DIA_ANEXION_GUANACASTE                        = 8;
    //AGOSTO
    const AGOSTO15_DIA_DE_LA_MADRE                              = 9;
    const AGOSTO31_DIA_DE_LA_CULTURA_AFROCOSTARRICENSE          = 10;
    //SETIEMBRE
    const SETIEMBRE9_DIA_DEL_NINO                               = 11;
    const SETIEMBRE15_DIA_DE_LA_INDEPENDENCIA                   = 12;
    //OCTUBRE
    const OCTUBRE12_DIA_DE_LAS_CULTURAS                         = 13;
    const OCTUBRE31_DIA_DE_LA_MASCARADA                         = 14;
    //NOVIEMBRE
    const NOVIEMBRE22_DIA_DEL_EDUCADOR                          = 15;
    //DICIEMBRE
    const DICIEMBRE1_DIA_DE_LA_ABOLICION_DEL_EJERCITO           = 16;
    const DICIEMBRE25_NAVIDAD                                   = 17;
    const DICIEMBRE31_VISPERA_NEW_YEAR                          = 18;

    private $dates = [
        1 => [ 'date' => '01/01', 'label' => 'Año nuevo', ],
        2 => [ 'date' => '02/14', 'label' => 'Día de San Valentín', ],
        3 => [ 'date' => '03/20', 'label' => 'Día del aniversario de la batalla de Santa Rosa', ],
        4 => [ 'date' => '04/11', 'label' => 'Día del aniversario de la batalla de Rivas', ],
        5 => [ 'date' => '05/01', 'label' => 'Día del trabajador', ],
        6 => [ 'date' => '06/05', 'label' => 'Día del medio ambiente', ],
        7 => [ 'date' => '06/21', 'label' => 'Día del Padre', ],
        8 => [ 'date' => '07/25', 'label' => 'Día de la anexión de Guanacaste', ],
        9 => [ 'date' => '08/15', 'label' => 'Día de la Madre', ],
        10 => [ 'date' => '08/31', 'label' => 'Día de la cultura afrocostarricense', ],
        11 => [ 'date' => '09/09', 'label' => 'Día del niño', ],
        12 => [ 'date' => '09/15', 'label' => 'Día de la independencia', ],
        13 => [ 'date' => '10/12', 'label' => 'Día del encuentro de culturas', ],
        14 => [ 'date' => '10/31', 'label' => 'Día de la mascarada', ],
        15 => [ 'date' => '11/22', 'label' => 'Día del educador', ],
        16 => [ 'date' => '12/01', 'label' => 'Abolición del ejército', ],
        17 => [ 'date' => '12/25', 'label' => 'Navidad', ],
        18 => [ 'date' => '12/31', 'label' => 'Víspera de Año nuevo', ]
    ];

    public function getDateString($year = 2020)
    {
        return $year.'/'.$this->dates[$this->value]['date'].' 0';
    }
    
    public function getDateHolidayName($year = 2020)
    {
        return $this->dates[$this->value]['label'];
    }
}
