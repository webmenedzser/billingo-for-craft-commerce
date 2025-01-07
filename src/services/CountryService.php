<?php

namespace webmenedzser\billingo\services;

use craft\base\Component;

class CountryService extends Component
{
    public const COUNTRIES = [
        'HU' => 'Magyarország',
        'AD' => 'Andorra',
        'AE' => 'Egyesült Arab Emírségek',
        'AF' => 'Afganisztán',
        'AG' => 'Antigua',
        'AI' => 'Anguilla',
        'AL' => 'Albánia',
        'AM' => 'Örményország',
        'AO' => 'Angola',
        'AQ' => 'Antarktisz',
        'AR' => 'Argentína',
        'AS' => 'Amerikai Szamoa',
        'AT' => 'Ausztria',
        'AU' => 'Ausztrália',
        'AW' => 'Aruba',
        'AX' => 'Aaland szigetek',
        'AZ' => 'Azerbajdzsán',
        'BA' => 'Bosznia-Hercegovina',
        'BB' => 'Barbados',
        'BD' => 'Banglades',
        'BE' => 'Belgium',
        'BF' => 'Burkina Faso',
        'BG' => 'Bulgária',
        'BH' => 'Bahrain',
        'BI' => 'Burundi',
        'BJ' => 'Benin',
        'BL' => 'Saint Barthélémy',
        'BM' => 'Bermuda',
        'BN' => 'Brunei',
        'BO' => 'Bolívia',
        'BQ' => 'Bonaire',
        'BR' => 'Brazília',
        'BS' => 'Bahama-szigetek Nassau',
        'BT' => 'Bhután',
        'BV' => 'Bouvet-sziget',
        'BW' => 'Botswana',
        'BY' => 'Fehéroroszország',
        'BZ' => 'Belize',
        'CA' => 'Kanada',
        'CC' => 'Kókusz (Keeling)-szigetek',
        'CD' => 'Kongói Demokratikus Köztársaság',
        'CF' => 'Közép-Afrikai Köztársaság',
        'CG' => 'Kongó',
        'CH' => 'Svájc',
        'CI' => 'Elefántcsontpart',
        'CK' => 'Cook-szigetek',
        'CL' => 'Chile',
        'CM' => 'Kamerun',
        'CN' => 'Kína',
        'CO' => 'Kolumbia',
        'CR' => 'Costa Rica',
        'CU' => 'Kuba',
        'CV' => 'Zöldfoki Köztársaság',
        'CW' => 'Curacao',
        'CX' => 'Karácsony-sziget',
        'CY' => 'Ciprus',
        'CZ' => 'Csehország',
        'DE' => 'Németország',
        'DJ' => 'Dzsibuti',
        'DK' => 'Dánia',
        'DM' => 'Dominika',
        'DO' => 'Dominikai Köztársaság',
        'DZ' => 'Algéria',
        'EC' => 'Equador',
        'EE' => 'Észtország',
        'EG' => 'Egyiptom',
        'EH' => 'Nyugat-Szahara',
        'ER' => 'Eritrea',
        'ES' => 'Spanyolország',
        'ET' => 'Etiópia',
        'FI' => 'Finnország',
        'FJ' => 'Fidzsi-szigetek',
        'FK' => 'Falkland-szigetek',
        'FM' => 'Mikronézia',
        'FO' => 'Faroe szigetek',
        'FR' => 'Franciaország',
        'FX' => 'France, metropolitan',
        'GA' => 'Gabon',
        'GB' => 'Egyesült Királyság (Nagy Britannia)',
        'GD' => 'Grenada',
        'GE' => 'Grúzia',
        'GF' => 'Francia Guiana',
        'GG' => 'Guernsey',
        'GH' => 'Ghana',
        'GI' => 'Gibraltár',
        'GL' => 'Grönland',
        'GM' => 'Gambia',
        'GN' => 'Guinea',
        'GP' => 'Guadeloupe',
        'GQ' => 'Egyenlítői Guinea',
        'GR' => 'Görögország',
        'GS' => 'Déli-Georgia és Déli-Sandwich-szigetek',
        'GT' => 'Guatemala',
        'GU' => 'Guam',
        'GW' => 'Bissau-Guinea',
        'GY' => 'Guyana',
        'HK' => 'Hongkong',
        'HM' => 'Heard-sziget és McDonalds-szigetek',
        'HN' => 'Honduras',
        'HR' => 'Horvátország',
        'HT' => 'Haiti',
        'ID' => 'Indonézia',
        'IE' => 'Írország',
        'IL' => 'Izrael',
        'IM' => 'Man sziget',
        'IN' => 'India',
        'IO' => 'Brit Indiai-Óceániai Terület',
        'IQ' => 'Irak',
        'IR' => 'Irán',
        'IS' => 'Izland',
        'IT' => 'Olaszország',
        'JE' => 'Jersey',
        'JM' => 'Jamaica',
        'JO' => 'Jordánia',
        'JP' => 'Japán',
        'KE' => 'Kenya',
        'KG' => 'Kirgizisztán',
        'KH' => 'Kambodzsa',
        'KI' => 'Kiribati Köztársaság Tuvalu',
        'KM' => 'Comore-szigetek',
        'KN' => 'Saint Christopher és Nevis',
        'KP' => 'Koreai NDK',
        'KR' => 'Dél Korea',
        'KW' => 'Kuwait',
        'KY' => 'Kajmán-szigetek',
        'KZ' => 'Kazahsztán',
        'LA' => 'Laosz',
        'LB' => 'Libanon',
        'LC' => 'Saint Lucia',
        'LI' => 'Liechtenstein',
        'LK' => 'Sri Lanka',
        'LR' => 'Liberia',
        'LS' => 'Lesotho',
        'LT' => 'Litvánia',
        'LU' => 'Luxemburg',
        'LV' => 'Lettország',
        'LY' => 'Líbia',
        'MA' => 'Marokkó',
        'MC' => 'Monaco',
        'MD' => 'Moldova',
        'ME' => 'Montenegro',
        'MF' => 'Saint Martin',
        'MG' => 'Malgas Köztársaság',
        'MH' => 'Marshall-szigetek',
        'MK' => 'Észak-Macedónia',
        'ML' => 'Mali',
        'MM' => 'Mianmar',
        'MN' => 'Mongólia',
        'MO' => 'Macao',
        'MP' => 'Északi-Mariana-szigetek',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MS' => 'Montserrat',
        'MT' => 'Málta',
        'MU' => 'Mauritius',
        'MV' => 'Maldív-szigetek',
        'MW' => 'Malawi',
        'MX' => 'Mexikó',
        'MY' => 'Malajzia',
        'MZ' => 'Mozambik',
        'NA' => 'Namíbia',
        'NC' => 'Új-Kaledónia',
        'NE' => 'Niger',
        'NF' => 'Norfolk szigetek',
        'NG' => 'Nigéria',
        'NI' => 'Nicaragua',
        'NL' => 'Hollandia',
        'NO' => 'Norvégia',
        'NP' => 'Nepál',
        'NR' => 'Nauru',
        'NU' => 'Niue',
        'NZ' => 'Új-Zéland',
        'OM' => 'Omán',
        'PA' => 'Panama',
        'PE' => 'Peru',
        'PF' => 'Francia Polinézia',
        'PG' => 'Pápua Új-Ginea',
        'PH' => 'Fülöp-szigetek',
        'PK' => 'Pakisztán',
        'PL' => 'Lengyelország',
        'PM' => 'Saint Pierre és Miquelon',
        'PN' => 'Pitcairn-sziget',
        'PR' => 'Puerto Rico',
        'PS' => 'Palesztína',
        'PT' => 'Portugália',
        'PW' => 'Palau',
        'PY' => 'Paraguay',
        'QA' => 'Quatar',
        'RE' => 'Reunion',
        'RO' => 'Románia',
        'RS' => 'Szerbia',
        'RU' => 'Oroszország',
        'RW' => 'Ruanda',
        'SA' => 'Szaud-Arábia',
        'SB' => 'Solomon-szigetek',
        'SC' => 'Seychelle-szigetek',
        'SD' => 'Szudán',
        'SE' => 'Svédország',
        'SG' => 'Szingapúr',
        'SH' => 'Szent Ilona',
        'SI' => 'Szlovénia',
        'SJ' => 'Svalbard és Jan Mayen',
        'SK' => 'Szlovákia',
        'SL' => 'Sierra Leone',
        'SM' => 'San Marino',
        'SN' => 'Szenegál',
        'SO' => 'Szomália',
        'SR' => 'Suriname',
        'SS' => 'Dél-Szudán',
        'ST' => 'Sao Tome és Principe',
        'SV' => 'Salvador',
        'SX' => 'St. Maarten',
        'SY' => 'Szíria',
        'SZ' => 'Szváziföld',
        'TC' => 'Turks- és Caicos-szigetek',
        'TD' => 'Csád',
        'TF' => 'Francia Déli Területek',
        'TG' => 'Togo',
        'TH' => 'Thaiföld',
        'TJ' => 'Tadzsikisztán',
        'TK' => 'Tokelau-szigetek',
        'TL' => 'Kelet-Timor',
        'TM' => 'Türkmenisztán',
        'TN' => 'Tunézia',
        'TO' => 'Tonga',
        'TR' => 'Törökország',
        'TT' => 'Trinidad és Tobago',
        'TV' => 'Tuvalu',
        'TW' => 'Taiwan',
        'TZ' => 'Tanzánia',
        'UA' => 'Ukrajna',
        'UG' => 'Uganda',
        'UM' => 'Amerikai Csendes-óceáni-Szigetek',
        'US' => 'Amerikai Egyesült Államok',
        'UY' => 'Uruguay',
        'UZ' => 'Üzbegisztán',
        'VA' => 'Vatikán',
        'VC' => 'Saint Vincent és Grenadines',
        'VE' => 'Venezuela',
        'VG' => 'Brit Virgin-szigetek',
        'VI' => 'Amerikai Virgin-szigetek',
        'VN' => 'Vietnámi Köztársaság',
        'VU' => 'Vanuatu',
        'W1' => 'Gáza és Jerikó',
        'WF' => 'Wallis és Futuna',
        'WS' => 'Nyugat-Szamoa',
        'XK' => 'Koszovó',
        'YE' => 'Jemeni Arab Köztársaság',
        'YT' => 'Mayotte',
        'ZA' => 'Dél-Afrikai Köztársaság',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    ];

    public static function getCountryNameByCode(string $countryCode) : string
    {
        return self::COUNTRIES[$countryCode] ?? '';
    }
}
