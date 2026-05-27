<?php

namespace App\Livewire\Forms;

use App\Models\BillTo;
use App\Models\Company;
use App\Models\Hub;
use App\Models\Product;
use App\Models\ShipTo;
use App\Models\Vendor;
use App\Services\AuthorizationService;
use App\Services\MaestrosApiService;
use App\Support\ContainerNumber;
use Livewire\Component;

class CreatePucharseOrder extends Component
{
    /**
     * Campos que nacen desde Seguimiento/API y no deben modificarse manualmente
     * desde el modo edición de PO.
     */
    private const EDIT_LOCKED_API_FIELDS = [
        'order_number',
        'vendor_id',
        'vendor_number',
        'retail_group',
        'route_label',
        'net_total',
        'total_amount',
        'currency',
        'emision_date_po',
        'category',
        'incoterms',
        'date_theorical_load',
        'factory_proforma_number',
        'reason',
        'customer_type',
    ];

    private const EDIT_LOCKED_TRACKING_DATE_FIELDS = [
        'date_etd_initial',
        'date_etd',
        'date_atd',
        'date_eta',
        'date_eta_updated',
        'date_ata',
    ];

    private const TRACKING_NOT_APPLICABLE_LOCKED_FIELDS = [
        'container_type',
        'container_number',
        'shipping_line',
    ];

    // Arrays para selects
    public $modalidadArray = ['op1' => 'Modalidad 1', 'op2' => 'Modalidad 2'];

    public $hubArray = ['1' => 'Hub 1', '2' => 'Hub 2'];

    public $paisArray = ['cr' => 'Costa Rica', 'us' => 'Estados Unidos'];

    public $estadoArray = ['cr' => 'San José', 'us' => 'Miami'];

    public $tiposIncotermArray = [
        'CIF' => 'CIF',
        'CIP' => 'CIP',
        'CFR' => 'CFR',
        'CPT' => 'CPT',
        'DAT' => 'DAT',
        'DAP' => 'DAP',
        'DDP' => 'DDP',
        'DEQ' => 'DEQ',
        'DES' => 'DES',
        'EXD' => 'EXD',
        'EXQ' => 'EXQ',
        'EXW' => 'EXW',
        'FCA' => 'FCA',
        'FOB' => 'FOB',
    ];

    public $currencyArray = ['CRC' => 'Colones', 'USD' => 'Dólar Estadounidense', 'EUR' => 'Euro'];

    public $paymentTermsArray = ['30' => '30 días', '60' => '60 días', '90' => '90 días'];

    public $vendorArray = [];

    public $shipToArray = [];

    // Arrays para dropdowns de datos maestros desde API
    public $tradingCompanyArray = [];

    public $departurePortArray = [];

    public $arrivalPortArray = [];

    public $serviceProviderArray = [];

    public $transportTypeArray = [];

    public $rateTypeArray = [];

    // Arrays para dropdowns de datos maestros (legacy - se reemplazará con API)
    public $containerTypeArray = [
        'Contenedor 20ft' => 'Contenedor 20ft',
        'Contenedor 40 HC' => 'Contenedor 40 HC',
        'Contenedor 40ft' => 'Contenedor 40ft',
        'Contenedor 45 HC' => 'Contenedor 45 HC',
        'Contenedor 45ft' => 'Contenedor 45ft',
        'Contenedor 53ft' => 'Contenedor 53ft',
        'FTL' => 'FTL',
        'LCL' => 'LCL',
        'Plataforma 20ft' => 'Plataforma 20ft',
        'Plataforma 40ft' => 'Plataforma 40ft',
    ];

    public $portsArray = [
        'ACAJUTLA, EL SALVADOR' => 'ACAJUTLA, EL SALVADOR',
        'ALAJUELA, COSTA RICA' => 'ALAJUELA, COSTA RICA',
        'ALGECIRAS, SPAIN' => 'ALGECIRAS, SPAIN',
        'ALTAMIRA, MÉXICO' => 'ALTAMIRA, MÉXICO',
        'AMATILLO, HONDURAS' => 'AMATILLO, HONDURAS',
        'ANCONA, ITALY' => 'ANCONA, ITALY',
        'ANTWERP LUX, LUXEMBOURG' => 'ANTWERP LUX, LUXEMBOURG',
        'ANTWERP, BELGIUM' => 'ANTWERP, BELGIUM',
        'ARICA, CHILE' => 'ARICA, CHILE',
        'ATLANTA, UNITED STATES' => 'ATLANTA, UNITED STATES',
        'BALBOA, PANAMA' => 'BALBOA, PANAMA',
        'BANGKOK, THAILAND' => 'BANGKOK, THAILAND',
        'BARCELONA, SPAIN' => 'BARCELONA, SPAIN',
        'BARRANQUILLA, COLOMBIA' => 'BARRANQUILLA, COLOMBIA',
        'BARRIOS, GUATEMALA' => 'BARRIOS, GUATEMALA',
        'BAVARIA, GERMANY' => 'BAVARIA, GERMANY',
        'BEIJIAO, CHINA' => 'BEIJIAO, CHINA',
        'BILBAO, SPAIN' => 'BILBAO, SPAIN',
        'BOGOTÁ, COLOMBIA' => 'BOGOTÁ, COLOMBIA',
        'BOSTON, UNITED STATES' => 'BOSTON, UNITED STATES',
        'BREMERHAVEN, GERMANY' => 'BREMERHAVEN, GERMANY',
        'BROOKLYN, UNITED STATES' => 'BROOKLYN, UNITED STATES',
        'BUENAVENTURA, COLOMBIA' => 'BUENAVENTURA, COLOMBIA',
        'BUENOS AIRES, ARGENTINA' => 'BUENOS AIRES, ARGENTINA',
        'BUSAN, KOREA' => 'BUSAN, KOREA',
        'CABELLO, VENEZUELA' => 'CABELLO, VENEZUELA',
        'CALDERA, COSTA RICA' => 'CALDERA, COSTA RICA',
        'CALI, COLOMBIA' => 'CALI, COLOMBIA',
        'CALIFORNIA, UNITED STATES' => 'CALIFORNIA, UNITED STATES',
        'CALLAO, PERU' => 'CALLAO, PERU',
        'CARABOBO, VENEZUELA' => 'CARABOBO, VENEZUELA',
        'CARTAGENA, COLOMBIA' => 'CARTAGENA, COLOMBIA',
        'CASTELLON, SPAIN' => 'CASTELLON, SPAIN',
        'CAUCEDO, DOMINICAN REPUBLIC' => 'CAUCEDO, DOMINICAN REPUBLIC',
        'CD HIDALGO, MÉXICO' => 'CD HIDALGO, MÉXICO',
        'CHANGCHENG, CHINA' => 'CHANGCHENG, CHINA',
        'CHARLESTON, UNITED STATES' => 'CHARLESTON, UNITED STATES',
        'CHIWAN, CHINA' => 'CHIWAN, CHINA',
        'CHONGQING, CHINA' => 'CHONGQING, CHINA',
        'CIUDAD DE GUATEMALA, GUATEMALA' => 'CIUDAD DE GUATEMALA, GUATEMALA',
        'CIUDAD HIDALGO, MÉXICO' => 'CIUDAD HIDALGO, MÉXICO',
        'COCHIN, INDIA' => 'COCHIN, INDIA',
        'COLON, PANAMA' => 'COLON, PANAMA',
        'COLON, PANAMÁ' => 'COLON, PANAMÁ',
        'CORTÉS, HONDURAS' => 'CORTÉS, HONDURAS',
        'CRANBURY, UNITED STATES' => 'CRANBURY, UNITED STATES',
        'CUCUTA, COLOMBIA' => 'CUCUTA, COLOMBIA',
        'CUNDINAMARCA, COLOMBIA' => 'CUNDINAMARCA, COLOMBIA',
        'CÚCUTA, COLOMBIA' => 'CÚCUTA, COLOMBIA',
        'DA NANG, VIETNAM' => 'DA NANG, VIETNAM',
        'DALIAN, CHINA' => 'DALIAN, CHINA',
        'DATO_ANTIGUO, COSTA RICA' => 'DATO_ANTIGUO, COSTA RICA',
        'DELMAS, HAITI' => 'DELMAS, HAITI',
        'DESAMPARADOS, COSTA RICA' => 'DESAMPARADOS, COSTA RICA',
        'DORAL, UNITED STATES' => 'DORAL, UNITED STATES',
        'DUCHCOV, CZECH REPUBLIC' => 'DUCHCOV, CZECH REPUBLIC',
        'EL PASO, UNITED STATES' => 'EL PASO, UNITED STATES',
        'ESMERALDA, ECUADOR' => 'ESMERALDA, ECUADOR',
        'EVERGLADES, UNITED STATES' => 'EVERGLADES, UNITED STATES',
        'EZEIZA, ARGENTINA' => 'EZEIZA, ARGENTINA',
        'FLORIDA, UNITED STATES' => 'FLORIDA, UNITED STATES',
        'FOSHAN NEW PORT, CHINA' => 'FOSHAN NEW PORT, CHINA',
        'FOSHAN, CHINA' => 'FOSHAN, CHINA',
        'FRANKFURT, GERMANY' => 'FRANKFURT, GERMANY',
        'FUJIAN, CHINA' => 'FUJIAN, CHINA',
        'FUNZA, COLOMBIA' => 'FUNZA, COLOMBIA',
        'FUZHOU, CHINA' => 'FUZHOU, CHINA',
        'GAOMING FOSHAN, CHINA' => 'GAOMING FOSHAN, CHINA',
        'GAOMING, CHINA' => 'GAOMING, CHINA',
        'GAOSHA, CHINA' => 'GAOSHA, CHINA',
        'GENOA, ITALY' => 'GENOA, ITALY',
        'GEORGE TOWN, CAYMAN ISLANDS' => 'GEORGE TOWN, CAYMAN ISLANDS',
        'GEORGIA, UNITED STATES' => 'GEORGIA, UNITED STATES',
        'GUADALAJARA, MÉXICO' => 'GUADALAJARA, MÉXICO',
        'GUANGDONG PROVINCE, CHINA' => 'GUANGDONG PROVINCE, CHINA',
        'GUANGDONG, CHINA' => 'GUANGDONG, CHINA',
        'GUANGZHOU, CHINA' => 'GUANGZHOU, CHINA',
        'GUARERO, VENEZUELA' => 'GUARERO, VENEZUELA',
        'GUAYAQUIL, ECUADOR' => 'GUAYAQUIL, ECUADOR',
        'GÉNOVA, ITALY' => 'GÉNOVA, ITALY',
        'HAI PHONG, VIETNAM' => 'HAI PHONG, VIETNAM',
        'HAIFA, ISRAEL' => 'HAIFA, ISRAEL',
        'HAMBURGO, GERMANY' => 'HAMBURGO, GERMANY',
        'HEBEI, CHINA' => 'HEBEI, CHINA',
        'HENAN, CHINA' => 'HENAN, CHINA',
        'HEREDIA, COSTA RICA' => 'HEREDIA, COSTA RICA',
        'HO CHI MINH, VIETNAM' => 'HO CHI MINH, VIETNAM',
        'HONG KONG, CHINA' => 'HONG KONG, CHINA',
        'HOUSTON, UNITED STATES' => 'HOUSTON, UNITED STATES',
        'HUANGPU, CHINA' => 'HUANGPU, CHINA',
        'ITAJAI, BRAZIL' => 'ITAJAI, BRAZIL',
        'JABEL ALI, UNITED ARAB EMIRATES' => 'JABEL ALI, UNITED ARAB EMIRATES',
        'JAXPORT, UNITED STATES' => 'JAXPORT, UNITED STATES',
        'JEBELI ALI, UNITED ARAB EMIRATES' => 'JEBELI ALI, UNITED ARAB EMIRATES',
        'JIANGMEN, CHINA' => 'JIANGMEN, CHINA',
        'JIANGSU, CHINA' => 'JIANGSU, CHINA',
        'JIUJIANG,  FOSHAN, CHINA' => 'JIUJIANG,  FOSHAN, CHINA',
        'KANSAS, UNITED STATES' => 'KANSAS, UNITED STATES',
        'KAOHSIUNG, TAIWAN' => 'KAOHSIUNG, TAIWAN',
        'KARACHI, PAKISTAN' => 'KARACHI, PAKISTAN',
        'KEELUNG, CHINA' => 'KEELUNG, CHINA',
        'KOPER LUX, LUXEMBOURG' => 'KOPER LUX, LUXEMBOURG',
        'KOPER, HUNGARY' => 'KOPER, HUNGARY',
        'LA GUAIRA, VENEZUELA' => 'LA GUAIRA, VENEZUELA',
        'LA SPEZIA, ITALY' => 'LA SPEZIA, ITALY',
        'LAEM CHABANG, THAILAND' => 'LAEM CHABANG, THAILAND',
        'LANCASTER, UNITED STATES' => 'LANCASTER, UNITED STATES',
        'LANSHI, CHINA' => 'LANSHI, CHINA',
        'LAZARO CARDENAS, MÉXICO' => 'LAZARO CARDENAS, MÉXICO',
        'LEIPHEIM, GERMANY' => 'LEIPHEIM, GERMANY',
        'LEIXÕES, PORTUGAL' => 'LEIXÕES, PORTUGAL',
        'LELIU, CHINA' => 'LELIU, CHINA',
        'LIMA, PERU' => 'LIMA, PERU',
        'LIMON, COSTA RICA' => 'LIMON, COSTA RICA',
        'LIVORNO, ITALY' => 'LIVORNO, ITALY',
        'LOS ANGELES, UNITED STATES' => 'LOS ANGELES, UNITED STATES',
        'MAFANG, CHINA' => 'MAFANG, CHINA',
        'MANILA, PHILIPPINES' => 'MANILA, PHILIPPINES',
        'MANIZALES, COLOMBIA' => 'MANIZALES, COLOMBIA',
        'MANZANILLO M, MÉXICO' => 'MANZANILLO M, MÉXICO',
        'MANZANILLO P, PANAMA' => 'MANZANILLO P, PANAMA',
        'MANZANILLO P, PANAMÁ' => 'MANZANILLO P, PANAMÁ',
        'MANZANILLO, CUBA' => 'MANZANILLO, CUBA',
        'MANZANILLO, MÉXICO' => 'MANZANILLO, MÉXICO',
        'MANZANILLO, PANAMA' => 'MANZANILLO, PANAMA',
        'MARACAIBO, VENEZUELA' => 'MARACAIBO, VENEZUELA',
        'MARIN, SPAIN' => 'MARIN, SPAIN',
        'MASSALAVES, SPAIN' => 'MASSALAVES, SPAIN',
        'MASSILLON OH, UNITED STATES' => 'MASSILLON OH, UNITED STATES',
        'MAWEI FUZHOU, CHINA' => 'MAWEI FUZHOU, CHINA',
        'MAWEI, CHINA' => 'MAWEI, CHINA',
        'MAWEI, FUJIAN, CHINA' => 'MAWEI, FUJIAN, CHINA',
        'MEDELLIN, COLOMBIA' => 'MEDELLIN, COLOMBIA',
        'MEXICO, PHILIPPINES' => 'MEXICO, PHILIPPINES',
        'MIAMI ESP, UNITED STATES' => 'MIAMI ESP, UNITED STATES',
        'MIAMI FL, UNITED STATES' => 'MIAMI FL, UNITED STATES',
        'MIAMI INTERPORT, UNITED STATES' => 'MIAMI INTERPORT, UNITED STATES',
        'MIAMI, UNITED STATES' => 'MIAMI, UNITED STATES',
        'MIGRACIÓN MIAMI, UNITED STATES' => 'MIGRACIÓN MIAMI, UNITED STATES',
        'MILAN, ITALY' => 'MILAN, ITALY',
        'MINNESOTA, UNITED STATES' => 'MINNESOTA, UNITED STATES',
        'MIRAMAR, UNITED STATES' => 'MIRAMAR, UNITED STATES',
        'MIXCO, GUATEMALA' => 'MIXCO, GUATEMALA',
        'MOIN, COSTA RICA' => 'MOIN, COSTA RICA',
        'MONTEVIDEO, URUGUAY' => 'MONTEVIDEO, URUGUAY',
        'MOÍN, COSTA RICA' => 'MOÍN, COSTA RICA',
        'MUNDRA, INDIA' => 'MUNDRA, INDIA',
        'MURCIA, SPAIN' => 'MURCIA, SPAIN',
        'NANJING, CHINA' => 'NANJING, CHINA',
        'NANSHA, CHINA' => 'NANSHA, CHINA',
        'NASSAU, BAHAMAS' => 'NASSAU, BAHAMAS',
        'NAVEGANTES, BRASIL, BRAZIL' => 'NAVEGANTES, BRASIL, BRAZIL',
        'NAVEGANTES, BRAZIL' => 'NAVEGANTES, BRAZIL',
        'NEW JERSEY, UNITED STATES' => 'NEW JERSEY, UNITED STATES',
        'NEW YORK, UNITED STATES' => 'NEW YORK, UNITED STATES',
        'NHAVA SHEVA, INDIA' => 'NHAVA SHEVA, INDIA',
        'NINGBO , CHINA' => 'NINGBO , CHINA',
        'NINGBO, CHINA' => 'NINGBO, CHINA',
        'NINGBO., CHINA' => 'NINGBO., CHINA',
        'NORFOLK VA, UNITED STATES' => 'NORFOLK VA, UNITED STATES',
        'NORFOLK, UNITED STATES' => 'NORFOLK, UNITED STATES',
        'NUEVO LEON, MÉXICO' => 'NUEVO LEON, MÉXICO',
        'OKLAHOMA, UNITED STATES' => 'OKLAHOMA, UNITED STATES',
        'OLOCUILTA, EL SALVADOR' => 'OLOCUILTA, EL SALVADOR',
        'OLOCUILTA, EL SALVADOR, EL SALVADOR' => 'OLOCUILTA, EL SALVADOR, EL SALVADOR',
        'ORANJESTAD, ARUBA' => 'ORANJESTAD, ARUBA',
        'PAITA, PERU' => 'PAITA, PERU',
        'PARAGUACHON, COLOMBIA' => 'PARAGUACHON, COLOMBIA',
        'PARAGUACHON, VENEZUELA' => 'PARAGUACHON, VENEZUELA',
        'PARAGUACHÓN, COLOMBIA' => 'PARAGUACHÓN, COLOMBIA',
        'PARANAGUA, BRAZIL' => 'PARANAGUA, BRAZIL',
        'PASO CANOAS, COSTA RICA' => 'PASO CANOAS, COSTA RICA',
        'PEDRO DE ALVARADO, GUATEMALA' => 'PEDRO DE ALVARADO, GUATEMALA',
        'PENSILVANIA, UNITED STATES' => 'PENSILVANIA, UNITED STATES',
        'PEÑAS BLANCAS, COSTA RICA' => 'PEÑAS BLANCAS, COSTA RICA',
        'PICKUP, COLOMBIA' => 'PICKUP, COLOMBIA',
        'PORTO ALEGRE, BRAZIL' => 'PORTO ALEGRE, BRAZIL',
        'POSORJA, ECUADOR' => 'POSORJA, ECUADOR',
        'PTO. ARICA, COLOMBIA' => 'PTO. ARICA, COLOMBIA',
        'PTO. CALLAO, PERU' => 'PTO. CALLAO, PERU',
        'PTO. CAUCEDO, DOMINICAN REPUBLIC' => 'PTO. CAUCEDO, DOMINICAN REPUBLIC',
        'PTO. PAITA, PERU' => 'PTO. PAITA, PERU',
        'PTO. SAN ANTONIO, CHILE' => 'PTO. SAN ANTONIO, CHILE',
        'PUERTO ARICA, PERU' => 'PUERTO ARICA, PERU',
        'PUERTO CABELLO, VENEZUELA' => 'PUERTO CABELLO, VENEZUELA',
        'PUERTO PLATA, DOMINICAN REPUBLIC' => 'PUERTO PLATA, DOMINICAN REPUBLIC',
        'PUERTO, UNITED STATES' => 'PUERTO, UNITED STATES',
        'PUERTO1, UNITED ARAB EMIRATES' => 'PUERTO1, UNITED ARAB EMIRATES',
        'QINGDAO, CHINA' => 'QINGDAO, CHINA',
        'QUETZAL, GUATEMALA' => 'QUETZAL, GUATEMALA',
        'QUI NHON, VIETNAM' => 'QUI NHON, VIETNAM',
        'QUITO, ECUADOR' => 'QUITO, ECUADOR',
        'RIO GRANDE, BRAZIL' => 'RIO GRANDE, BRAZIL',
        'RONGQI, CHINA' => 'RONGQI, CHINA',
        'ROTTERDAM, NEDERLAND' => 'ROTTERDAM, NEDERLAND',
        'SAN ANTONIO, CHILE' => 'SAN ANTONIO, CHILE',
        'SAN JOSE, COSTA RICA' => 'SAN JOSE, COSTA RICA',
        'SAN JUAN, PUERTO RICO' => 'SAN JUAN, PUERTO RICO',
        'SAN MARCOS, EL SALVADOR' => 'SAN MARCOS, EL SALVADOR',
        'SAN PEDRO DE SULA, HONDURAS' => 'SAN PEDRO DE SULA, HONDURAS',
        'SAN PEDRO, ARGENTINA' => 'SAN PEDRO, ARGENTINA',
        'SAN SALVADOR, EL SALVADOR' => 'SAN SALVADOR, EL SALVADOR',
        'SAN VICENTE, CHILE' => 'SAN VICENTE, CHILE',
        'SANRONG, CHINA' => 'SANRONG, CHINA',
        'SANSHAN, CHINA' => 'SANSHAN, CHINA',
        'SANSHUI FOSHAN, CHINA' => 'SANSHUI FOSHAN, CHINA',
        'SANSHUI, CHINA' => 'SANSHUI, CHINA',
        'SANTA MARIA, SPAIN' => 'SANTA MARIA, SPAIN',
        'SANTO DOMINGO, DOMINICAN REPUBLIC' => 'SANTO DOMINGO, DOMINICAN REPUBLIC',
        'SANTO TOMAS DE CASTILLA, GUATEMALA' => 'SANTO TOMAS DE CASTILLA, GUATEMALA',
        'SANTOS, BRAZIL' => 'SANTOS, BRAZIL',
        'SAO PAULO, BRAZIL' => 'SAO PAULO, BRAZIL',
        'SAVANNAH GEO, UNITED STATES' => 'SAVANNAH GEO, UNITED STATES',
        'SAVANNAH, UNITED STATES' => 'SAVANNAH, UNITED STATES',
        'SHANDON, CHINA' => 'SHANDON, CHINA',
        'SHANGHAI, CHINA' => 'SHANGHAI, CHINA',
        'SHANTOU, CHINA' => 'SHANTOU, CHINA',
        'SHEKOU, CHINA' => 'SHEKOU, CHINA',
        'SHENZHEN, CHINA' => 'SHENZHEN, CHINA',
        'SHUNDE, CHINA' => 'SHUNDE, CHINA',
        'SINES, PORTUGAL' => 'SINES, PORTUGAL',
        'SINGAPORE, SINGAPORE' => 'SINGAPORE, SINGAPORE',
        'STUHR, GERMANY' => 'STUHR, GERMANY',
        'SUAPE, BRAZIL' => 'SUAPE, BRAZIL',
        'SUBIC BAY, PHILIPPINES' => 'SUBIC BAY, PHILIPPINES',
        'SUZHOU, CHINA' => 'SUZHOU, CHINA',
        'TAICANG, CHINA' => 'TAICANG, CHINA',
        'TAICHUNG, CHINA' => 'TAICHUNG, CHINA',
        'TAIPEI TW, TAIWAN' => 'TAIPEI TW, TAIWAN',
        'TAIPEI, CHINA' => 'TAIPEI, CHINA',
        'TAIPEI, TAIWAN' => 'TAIPEI, TAIWAN',
        'TANGSHAN, CHINA' => 'TANGSHAN, CHINA',
        'TIANJIN, CHINA' => 'TIANJIN, CHINA',
        'TIANJIN,, CHINA' => 'TIANJIN,, CHINA',
        'TIANJINXINGANG, CHINA' => 'TIANJINXINGANG, CHINA',
        'TIENDITAS, VENEZUELA' => 'TIENDITAS, VENEZUELA',
        'TIFTON, UNITED STATES' => 'TIFTON, UNITED STATES',
        'TORONTO, CANADA' => 'TORONTO, CANADA',
        'TOWNSEND, UNITED STATES' => 'TOWNSEND, UNITED STATES',
        'TRUJILLO ALTO, PUERTO RICO' => 'TRUJILLO ALTO, PUERTO RICO',
        'VADO LIGURE, ITALY' => 'VADO LIGURE, ITALY',
        'VALENCIA CA, VENEZUELA' => 'VALENCIA CA, VENEZUELA',
        'VALENCIA, SPAIN' => 'VALENCIA, SPAIN',
        'VALENCIA, VENEZUELA' => 'VALENCIA, VENEZUELA',
        'VALPARAISO, CHILE' => 'VALPARAISO, CHILE',
        'VERACRUZ, MÉXICO' => 'VERACRUZ, MÉXICO',
        'VIGO, SPAIN' => 'VIGO, SPAIN',
        'WAIHAI, CHINA' => 'WAIHAI, CHINA',
        'WINFIELD, UNITED STATES' => 'WINFIELD, UNITED STATES',
        'WUHAN, CHINA' => 'WUHAN, CHINA',
        'XIAMEN, CHINA' => 'XIAMEN, CHINA',
        'XIAOLAN, CHINA' => 'XIAOLAN, CHINA',
        'XINGANG, CHINA' => 'XINGANG, CHINA',
        'XIOLAN, CHINA' => 'XIOLAN, CHINA',
        'YANTIAN, CHINA' => 'YANTIAN, CHINA',
        'YICHANG, CHINA' => 'YICHANG, CHINA',
        'YUMBO, COLOMBIA' => 'YUMBO, COLOMBIA',
        'YUNFU, CHINA' => 'YUNFU, CHINA',
        'ZHANGJIAGANG, CHINA' => 'ZHANGJIAGANG, CHINA',
        'ZHAOQING, CHINA' => 'ZHAOQING, CHINA',
        'ZHAPU, CHINA' => 'ZHAPU, CHINA',
        'ZHEJIANG, CHINA' => 'ZHEJIANG, CHINA',
        'ZHONGSHAN, CHINA' => 'ZHONGSHAN, CHINA',
    ];

    public $shippingLineArray = [
        'CMA CGM' => 'CMA CGM',
        'COSCO' => 'COSCO',
        'COSIARMA' => 'COSIARMA',
        'EVERGREEN' => 'EVERGREEN',
        'HAPAG LLOYD' => 'HAPAG LLOYD',
        'HAPPAG' => 'HAPPAG',
        'HSUD' => 'HSUD',
        'MAERSK' => 'MAERSK',
        'MSC' => 'MSC',
        'ONE' => 'ONE',
        'OOCL' => 'OOCL',
        'PIL' => 'PIL',
        'PRUEBA' => 'PRUEBA',
        'SEALAND' => 'SEALAND',
        'VASI' => 'VASI',
        'WAN HAI' => 'WAN HAI',
        'ZIM' => 'ZIM',
        'ZIM LINES' => 'ZIM LINES',
    ];

    public $routeLabelArray = [
        'Directo AR - Cencosud S.A.' => 'Directo AR - Cencosud S.A.',
        'Directo AR - Comercial Taffel SRL' => 'Directo AR - Comercial Taffel SRL',
        'Directo AR - Diefe Import Total S.R.L' => 'Directo AR - Diefe Import Total S.R.L',
        'Directo CL - Cencosud Colombia S.A.' => 'Directo CL - Cencosud Colombia S.A.',
        'Directo CL - COMERCIAL K SPA' => 'Directo CL - COMERCIAL K SPA',
        'Directo CL - Easy Retail S.A' => 'Directo CL - Easy Retail S.A',
        'Directo CL - Sodimac Colombia SA' => 'Directo CL - Sodimac Colombia SA',
        'Directo CL - Sodimac S.A.' => 'Directo CL - Sodimac S.A.',
        'Directo CO - Cencosud Colombia S.A.' => 'Directo CO - Cencosud Colombia S.A.',
        'Directo CO - Decoramia S.A.S.' => 'Directo CO - Decoramia S.A.S.',
        'Directo CO - Gricol S.A.' => 'Directo CO - Gricol S.A.',
        'Directo CO - MUNDIAL DE PARTES S.A.S' => 'Directo CO - MUNDIAL DE PARTES S.A.S',
        'Directo CO - Sodimac Colombia SA' => 'Directo CO - Sodimac Colombia SA',
        'Directo CR - Calle Antica' => 'Directo CR - Calle Antica',
        'Directo CR - Calle Cantina S.A.' => 'Directo CR - Calle Cantina S.A.',
        'Directo CR - Consorcio Ferretero San Jose S.A' => 'Directo CR - Consorcio Ferretero San Jose S.A',
        'Directo CR - CONSORCIO FERRETERO DE SAN JOSE, S.A.' => 'Directo CR - CONSORCIO FERRETERO DE SAN JOSE, S.A.',
        'Directo CR - FERRETERIA EPA, S.A.' => 'Directo CR - FERRETERIA EPA, S.A.',
        'Directo CR - OVERSEAS LOGISTICS OPERATIONS' => 'Directo CR - OVERSEAS LOGISTICS OPERATIONS',
        'Directo DO - Bellon S.A.S' => 'Directo DO - Bellon S.A.S',
        'Directo EC - Harmony Bamerica (Ecuador)' => 'Directo EC - Harmony Bamerica (Ecuador)',
        'Directo EC - Homecenter Ecuatorianos S.A.S (127597)' => 'Directo EC - Homecenter Ecuatorianos S.A.S (127597)',
        'Directo EC - Homecenters Ecuatorianos S.A.S' => 'Directo EC - Homecenters Ecuatorianos S.A.S',
        'Directo GT - FERRETERIA EPA, S.A.' => 'Directo GT - FERRETERIA EPA, S.A.',
        'Directo GT - FERRETERIA EPA, S.A. (Guatemala)' => 'Directo GT - FERRETERIA EPA, S.A. (Guatemala)',
        'Directo GT - Grupo Barro S.A' => 'Directo GT - Grupo Barro S.A',
        'Directo MX - Harmony Dropship Monterrey Mex' => 'Directo MX - Harmony Dropship Monterrey Mex',
        'Directo MX - Supermercados Internacionales HEB SA de CV.' => 'Directo MX - Supermercados Internacionales HEB SA de CV.',
        'Directo MX - Tiendas Soriana S.A De C.V' => 'Directo MX - Tiendas Soriana S.A De C.V',
        'Directo MX - VALVULAS Y ACCESORIOS APOLO SA DE CV' => 'Directo MX - VALVULAS Y ACCESORIOS APOLO SA DE CV',
        'Directo OL - BAMERICA CORPORATION' => 'Directo OL - BAMERICA CORPORATION',
        'Directo OL - FERRETERIA EPA, S.A.' => 'Directo OL - FERRETERIA EPA, S.A.',
        'Directo OL - FERRETERIA EPA, S.A. DE C.V.' => 'Directo OL - FERRETERIA EPA, S.A. DE C.V.',
        'Directo OL - OVERSEAS LOGISTICS OPERATIONS' => 'Directo OL - OVERSEAS LOGISTICS OPERATIONS',
        'Directo PA - Asia Trade Imp. & Exp. Inc' => 'Directo PA - Asia Trade Imp. & Exp. Inc',
        'Directo PE' => 'Directo PE',
        'Directo Pe - Harmony Bamerica (Perú)' => 'Directo Pe - Harmony Bamerica (Perú)',
        'Directo PE - Homecenters Peruanos S.A.' => 'Directo PE - Homecenters Peruanos S.A.',
        'Directo PE - Homecenters Peruanos S.A. (348706)' => 'Directo PE - Homecenters Peruanos S.A. (348706)',
        'Directo PE - Implementos Peru S.A.C' => 'Directo PE - Implementos Peru S.A.C',
        'Directo PE - Promart Perú' => 'Directo PE - Promart Perú',
        'Directo PE - Supermercados Peruanos S.A (527044)' => 'Directo PE - Supermercados Peruanos S.A (527044)',
        'Directo PE - Tiendas Del Mejoramiento Del Hogar S.A.' => 'Directo PE - Tiendas Del Mejoramiento Del Hogar S.A.',
        'Directo PR - A.Z.C. Metropolitan Distributors, Inc.' => 'Directo PR - A.Z.C. Metropolitan Distributors, Inc.',
        'Directo SV - Ferreteria Epa, S.A. De C.V' => 'Directo SV - Ferreteria Epa, S.A. De C.V',
        'Directo SV - FERRETERIA EPA, S.A. DE C.V.' => 'Directo SV - FERRETERIA EPA, S.A. DE C.V.',
        'Directo US - BAMERICA CORPORATION' => 'Directo US - BAMERICA CORPORATION',
        'Directo US - Hagalo Home Center (12640)' => 'Directo US - Hagalo Home Center (12640)',
        'Directo UY - Homecenter Sodimac S.A' => 'Directo UY - Homecenter Sodimac S.A',
        'Directo VE - Al Thompson\'s - STORE' => 'Directo VE - Al Thompson\'s - STORE',
        'Directo VE - BEVAL, C.A.' => 'Directo VE - BEVAL, C.A.',
        'Directo VE - Centrobeco C.A.' => 'Directo VE - Centrobeco C.A.',
        'Directo VE - CENTROBECO, C.A.' => 'Directo VE - CENTROBECO, C.A.',
        'Directo VE - FEBECA C.A.' => 'Directo VE - FEBECA C.A.',
        'Directo VE - Febeca, C.A.' => 'Directo VE - Febeca, C.A.',
        'Directo VE - FERRETERIA EPA, C.A.' => 'Directo VE - FERRETERIA EPA, C.A.',
        'Directo VE - LT 2015, C.A.' => 'Directo VE - LT 2015, C.A.',
        'Directo VE - SILLACA, C.A.' => 'Directo VE - SILLACA, C.A.',
        'GL GT - FERRETERIA EPA, S.A. (Guatemala)' => 'GL GT - FERRETERIA EPA, S.A. (Guatemala)',
        'GL GT - FERRETERIA EPA, S.A. DE C.V.' => 'GL GT - FERRETERIA EPA, S.A. DE C.V.',
        'GL GT - Michatoya Pacifico' => 'GL GT - Michatoya Pacifico',
        'GL SV - FERRETERIA EPA, S.A. (Guatemala)' => 'GL SV - FERRETERIA EPA, S.A. (Guatemala)',
        'GL SV - FERRETERIA EPA, S.A. DE C.V.' => 'GL SV - FERRETERIA EPA, S.A. DE C.V.',
        'GL SV - OVERSEAS LOGISTICS OPERATIONS' => 'GL SV - OVERSEAS LOGISTICS OPERATIONS',
        'ZF - Centrobeco C.A.' => 'ZF - Centrobeco C.A.',
        'ZF - CONSORCIO FERRETERO DE SAN JOSE, S.A.' => 'ZF - CONSORCIO FERRETERO DE SAN JOSE, S.A.',
        'ZF - FEBECA C.A.' => 'ZF - FEBECA C.A.',
        'ZF - FERRETERIA EPA, C.A.' => 'ZF - FERRETERIA EPA, C.A.',
        'ZF - FERRETERIA EPA, S.A.' => 'ZF - FERRETERIA EPA, S.A.',
        'ZF - FERRETERIA EPA, S.A. (Guatemala)' => 'ZF - FERRETERIA EPA, S.A. (Guatemala)',
        'ZF - FERRETERIA EPA, S.A. DE C.V.' => 'ZF - FERRETERIA EPA, S.A. DE C.V.',
        'ZF - OVERSEAS LOGISTICS OPERATIONS' => 'ZF - OVERSEAS LOGISTICS OPERATIONS',
    ];

    public $tariffTypeArray = [
        'FAK' => 'FAK',
        'NAC' => 'NAC',
        'Premium' => 'Premium',
    ];

    // Datos generales
    public $order_number;

    public $status;

    public $notes;

    public $company_id;

    // Vendor information
    public $vendor_id;

    public $vendor_direccion;

    public $vendor_pais;

    public $vendor_telefono;

    // Ship to information
    public $ship_to_id;

    public $ship_to_nombre;

    public $ship_to_direccion;

    public $ship_to_pais;

    public $ship_to_telefono;

    // Bill to information
    public $bill_to_id;

    public $bill_to_nombre;

    public $bill_to_direccion;

    public $bill_to_pais;

    public $bill_to_telefono;

    // Order details
    public $order_date;

    public $currency;

    public $incoterms;

    public $payment_terms;

    public $order_place;

    public $email_agent;

    // Totals
    public $net_total = 0.0;

    public $additional_cost = 0.0;

    public $total = 0.0;

    public $insurance_cost = 0.0;

    // Dimensiones
    public $largo;

    public $ancho;

    public $alto;

    public $volumen;

    public $peso_kg;

    public $peso_lb;

    // Fechas
    public $date_required_in_destination;

    public $date_planned_pickup;

    public $date_actual_pickup;

    public $date_estimated_hub_arrival;

    public $date_actual_hub_arrival;

    public $date_etd;

    public $date_atd;

    public $date_eta;

    public $date_ata;

    public $date_consolidation;

    public $release_date;

    // Productos
    public $orderProducts = [];

    public $searchTerm = '';

    public $searchResults = [];

    public $selectedProduct = null;

    public $quantity = 1;

    public $length_cm = 0;

    public $width_cm = 0;

    public $height_cm = 0;

    public $id;

    public $purchaseOrder;

    public $planned_hub_id;

    public $actual_hub_id;

    public $hubsArray = [];

    public $billToArray = [];

    // Material type options
    public $materialTypeOptions = [
        'dangerous' => 'Peligroso',
        'general' => 'General',
        'exclusive' => 'Exclusivo',
        'estibable' => 'Estibable',
    ];

    // New fields
    public $material_type = [];

    public $ensurence_type;

    public $mode;

    public $tracking_id;

    public $pallet_quantity;

    public $pallet_quantity_real;

    public $bill_of_lading;

    public $ground_transport_cost_1 = 0;

    public $ground_transport_cost_2 = 0;

    public $cost_nationalization = 0;

    public $cost_ofr_estimated = 0;

    public $cost_ofr_real = 0;

    public $estimated_pallet_cost = 0;

    public $real_cost_estimated_po = 0;

    public $real_cost_real_po = 0;

    public $other_costs = 0;

    public $other_expenses = 0;

    public $variable_calculare_weight = 0;

    public $savings_ofr_fcl = 0;

    public $saving_pickup = 0;

    public $saving_executed = 0;

    public $saving_not_executed = 0;

    public $comments;

    // Dimensiones
    public $largo_cm;

    public $ancho_cm;

    public $alto_cm;

    // Añade esta propiedad junto con las otras propiedades de dimensiones
    public $pallets;

    // Estado de carga para la API de maestros
    public $isLoadingMaestros = false;

    // Watchers
    protected $listeners = [
        'vendorSelected' => 'onVendorSelected',
        'shipToSelected' => 'onShipToSelected',
        'billToSelected' => 'onBillToSelected',
    ];

    // ===== Campos nuevos para OLO =====
    // Identificadores y transporte
    public $factory_proforma_number;

    public $mbl_number;

    public $container_type;

    public $container_number;

    public $shipping_line;

    public $port_of_loading_validated = false;

    // Flags / opciones
    public $is_dropship = false;

    public $applies_tlc = false;

    public $applies_af = false;

    public $has_facture_merca = false;

    public $used_rate_ok = false;

    public $uses_bonded_warehouse = false;

    public $apply_technical_note = false;

    public $etd_initial_validated = false;

    // Fechas/hitos
    public $date_booking_request;

    public $date_booking_authorized;

    public $date_theorical_load;

    public $date_variable_date;

    public $carga_lista_validada = false;

    public $date_received;

    public $date_etd_initial;

    public $inspection_date;

    public $vgm_cut_date;

    public $balance_payment_date;

    public $local_charges_payment_date;

    public $bonded_warehouse_enter;

    public $bonded_warehouse_exit;

    public $receipt_note_date;

    public $estimated_dc_availability_date;

    // Datos de negocio
    public $logistics_incoterm;

    public $price_incoterm;

    public $reason;

    public $category;

    public $forwarder_name;

    public $cargo_invoice_number;

    public $tariff_type;

    public $route_label;

    public $retail_group;

    public $customer_type;

    public $trading_company;

    public $service_provider;

    public $customs_dua;

    public $invoice;

    public $factura_merca;

    public $case_number_file;

    public $receipt_note;

    public $visibility_notes;

    // Puertos
    public $departure_port;

    public $arrival_port;

    // Estado de llegada
    public $arrival_status;

    public $delay_days;

    // Versiones actualizadas ETA/ETD
    public $date_eta_initial;

    public $date_eta_updated;

    // Costos
    public $po_amount = 0.0;        // Monto PO "declarado" (si lo usas)

    public $Invoice_amount = 0.0;   // Monto de la factura (mantengo el nombre exacto)

    public $freight_amount = 0.0;   // Monto flete

    public $total_amount = 0.0;     // Monto total

    // Métricas / contadores
    public $container_free_days;        // int

    public $etd_dates_difference;       // int (días)

    public $eta_dates_difference;       // int (días)

    // ===== Documentos (recepción) =====
    public $date_invoice_received;            // Fecha recepción de factura

    public $date_vendor_document_received;    // Fecha recepción doc. proveedor

    // Campos extras que faltaban
    public $cbm;

    public $dif_load_date;

    public $consolidator_name;

    public $vendor_number;

    public $emision_date_po;

    public $forwader_date;

    public bool $trackingDatesLocked = false;

    public bool $etdInitialLocked = false;

    public bool $tracking_not_applicable = false;

    public $tracking_not_applicable_reason;

    public bool $trackingNotApplicableApproved = false;

    public bool $trackingNotApplicablePending = false;

    public function mount($id = null)
    {
        $this->id = $id;
        // Ordenar rutas logísticas alfabéticamente
        ksort($this->routeLabelArray);
        $this->loadHubs();
        $this->loadBillTo();
        $this->calculateTotals();

        // Initialize with empty array for new records
        $this->material_type = ['general'];
        $this->ensurence_type = 'pending';

        if ($this->id) {
            $this->purchaseOrder = \App\Models\PurchaseOrder::with('products')->find($this->id);

            if ($this->purchaseOrder) {
                $this->tracking_not_applicable = (bool) ($this->purchaseOrder->tracking_not_applicable ?? false);
                $this->trackingNotApplicableApproved = (bool) ($this->purchaseOrder->tracking_not_applicable ?? false);
                $this->tracking_not_applicable_reason = $this->purchaseOrder->tracking_not_applicable_reason;
                $this->trackingNotApplicablePending = $this->purchaseOrder->hasAuthorizationPending('tracking_not_applicable');
                $this->trackingDatesLocked = $this->shouldLockTrackingDateFields($this->purchaseOrder);
                $this->etdInitialLocked = $this->shouldLockEtdInitial($this->purchaseOrder);
                if ($this->trackingNotApplicablePending && blank($this->tracking_not_applicable_reason)) {
                    $pendingTrackingAuthorization = $this->purchaseOrder->findAuthorizationByType('tracking_not_applicable', \App\Models\Authorization::STATUS_PENDING);
                    $this->tracking_not_applicable_reason = $pendingTrackingAuthorization?->data['reason'] ?? null;
                }

                // Cargar datos generales
                $this->order_number = $this->purchaseOrder->order_number;
                $this->status = $this->purchaseOrder->status;
                $this->notes = $this->purchaseOrder->notes;
                $this->company_id = $this->purchaseOrder->company_id;

                // Vendor information: el select usa vendo_code (código canónico), no vendors.id
                $this->vendor_number = $this->purchaseOrder->vendor_number ?? $this->purchaseOrder->vendor?->vendo_code ?? '';
                $this->vendor_id = $this->vendor_number;
                $this->vendor_direccion = $this->purchaseOrder->vendor_direccion;
                $this->vendor_pais = $this->purchaseOrder->vendor_pais;
                $this->vendor_telefono = $this->purchaseOrder->vendor_telefono;

                // Ship to information
                $this->ship_to_id = $this->purchaseOrder->ship_to_id;
                $this->ship_to_nombre = $this->purchaseOrder->ship_to_nombre;
                $this->ship_to_direccion = $this->purchaseOrder->ship_to_direccion;
                $this->ship_to_pais = $this->purchaseOrder->ship_to_pais;
                $this->ship_to_telefono = $this->purchaseOrder->ship_to_telefono;

                // Bill to information
                $this->bill_to_id = $this->purchaseOrder->bill_to_id;
                $this->bill_to_nombre = $this->purchaseOrder->bill_to_nombre;
                $this->bill_to_direccion = $this->purchaseOrder->bill_to_direccion;
                $this->bill_to_pais = $this->purchaseOrder->bill_to_pais;
                $this->bill_to_telefono = $this->purchaseOrder->bill_to_telefono;

                // Order details
                $this->order_date = $this->purchaseOrder->order_date ? $this->purchaseOrder->order_date->format('Y-m-d') : null;
                $this->currency = $this->purchaseOrder->currency;
                $this->incoterms = $this->purchaseOrder->incoterms;
                $this->payment_terms = $this->purchaseOrder->payment_terms;
                $this->price_incoterm = $this->purchaseOrder->price_incoterm;
                $this->order_place = $this->purchaseOrder->order_place;
                $this->email_agent = $this->purchaseOrder->email_agent;

                // Special handling for material_type which is now JSON
                if ($this->purchaseOrder->material_type) {
                    try {
                        // If it's a JSON string, decode it
                        if (is_string($this->purchaseOrder->material_type)) {
                            $this->material_type = json_decode($this->purchaseOrder->material_type, true) ?? ['general'];
                        } else {
                            // If it's already an array (Laravel might have auto-cast it)
                            $this->material_type = (array) $this->purchaseOrder->material_type;
                        }
                    } catch (\Exception $e) {
                        // Fallback to default if there's an error
                        $this->material_type = ['general'];
                    }
                } else {
                    // Default value if nothing is stored
                    $this->material_type = ['general'];
                }

                // Totals
                $this->net_total = $this->purchaseOrder->net_total;
                $this->additional_cost = $this->purchaseOrder->additional_cost;
                $this->total = $this->purchaseOrder->total;
                $this->insurance_cost = $this->purchaseOrder->insurance_cost;

                // Dimensiones
                $this->largo = $this->purchaseOrder->length;
                $this->ancho = $this->purchaseOrder->width;
                $this->alto = $this->purchaseOrder->height;
                $this->volumen = $this->purchaseOrder->volume;
                $this->peso_kg = $this->purchaseOrder->weight_kg ? (float) $this->purchaseOrder->weight_kg : null;
                $this->peso_lb = $this->purchaseOrder->weight_lb ? (float) $this->purchaseOrder->weight_lb : null;
                $this->tracking_id = $this->purchaseOrder->tracking_id;
                $this->pallet_quantity = $this->purchaseOrder->pallet_quantity;
                $this->pallet_quantity_real = $this->purchaseOrder->pallet_quantity_real;
                $this->bill_of_lading = $this->purchaseOrder->bill_of_lading;
                $this->ground_transport_cost_1 = $this->purchaseOrder->ground_transport_cost_1;
                $this->ground_transport_cost_2 = $this->purchaseOrder->ground_transport_cost_2;
                $this->cost_nationalization = $this->purchaseOrder->cost_nationalization;
                $this->cost_ofr_estimated = $this->purchaseOrder->cost_ofr_estimated;
                $this->cost_ofr_real = $this->purchaseOrder->cost_ofr_real;
                $this->estimated_pallet_cost = $this->purchaseOrder->estimated_pallet_cost;
                $this->real_cost_estimated_po = $this->purchaseOrder->real_cost_estimated_po;
                $this->real_cost_real_po = $this->purchaseOrder->real_cost_real_po;
                $this->other_costs = $this->purchaseOrder->other_costs;
                $this->other_expenses = $this->purchaseOrder->other_expenses;
                $this->savings_ofr_fcl = $this->purchaseOrder->savings_ofr_fcl;

                // Fechas
                $this->date_required_in_destination = $this->purchaseOrder->date_required_in_destination ? $this->purchaseOrder->date_required_in_destination->format('Y-m-d') : null;
                $this->date_planned_pickup = $this->purchaseOrder->date_planned_pickup ? $this->purchaseOrder->date_planned_pickup->format('Y-m-d') : null;
                $this->date_actual_pickup = $this->purchaseOrder->date_actual_pickup ? $this->purchaseOrder->date_actual_pickup->format('Y-m-d') : null;
                $this->date_estimated_hub_arrival = $this->purchaseOrder->date_estimated_hub_arrival ? $this->purchaseOrder->date_estimated_hub_arrival->format('Y-m-d') : null;
                $this->date_actual_hub_arrival = $this->purchaseOrder->date_actual_hub_arrival ? $this->purchaseOrder->date_actual_hub_arrival->format('Y-m-d') : null;
                $this->date_etd = $this->purchaseOrder->date_etd ? $this->purchaseOrder->date_etd->format('Y-m-d') : null;
                $this->date_atd = $this->purchaseOrder->date_atd ? $this->purchaseOrder->date_atd->format('Y-m-d') : null;
                $this->date_eta = $this->purchaseOrder->date_eta ? $this->purchaseOrder->date_eta->format('Y-m-d') : null;
                $this->date_ata = $this->purchaseOrder->date_ata ? $this->purchaseOrder->date_ata->format('Y-m-d') : null;
                $this->date_consolidation = $this->purchaseOrder->date_consolidation ? $this->purchaseOrder->date_consolidation->format('Y-m-d') : null;
                $this->release_date = $this->purchaseOrder->release_date ? $this->purchaseOrder->release_date->format('Y-m-d') : null;
                $this->date_invoice_received = $this->purchaseOrder->date_invoice_received ? $this->purchaseOrder->date_invoice_received->format('Y-m-d') : null;
                $this->date_vendor_document_received = $this->purchaseOrder->date_vendor_document_received ? $this->purchaseOrder->date_vendor_document_received->format('Y-m-d') : null;

                $this->planned_hub_id = $this->purchaseOrder->planned_hub_id;
                $this->actual_hub_id = $this->purchaseOrder->actual_hub_id;
                $this->length_cm = $this->purchaseOrder->length_cm;
                $this->width_cm = $this->purchaseOrder->width_cm;
                $this->height_cm = $this->purchaseOrder->height_cm;

                // Cargar el modo de transporte
                $this->mode = $this->purchaseOrder->mode;

                // Cargar el tipo de seguro
                $this->ensurence_type = $this->purchaseOrder->ensurence_type ?? 'pending';

                // === Datos nuevos para OLO ===
                $this->factory_proforma_number = $this->purchaseOrder->factory_proforma_number;
                $this->mbl_number = $this->purchaseOrder->mbl_number;
                $this->container_type = $this->purchaseOrder->container_type;
                $this->container_number = $this->purchaseOrder->container_number;
                $this->shipping_line = $this->purchaseOrder->shipping_line;
                $this->port_of_loading_validated = (bool) ($this->purchaseOrder->port_of_loading_validated ?? false);

                $this->is_dropship = $this->purchaseOrder->is_dropship;
                $this->applies_tlc = $this->purchaseOrder->applies_tlc;
                $this->applies_af = $this->purchaseOrder->applies_af;
                $this->has_facture_merca = (bool) ($this->purchaseOrder->has_facture_merca ?? false);
                $this->used_rate_ok = (bool) ($this->purchaseOrder->used_rate_ok ?? false);
                $this->uses_bonded_warehouse = (bool) ($this->purchaseOrder->uses_bonded_warehouse ?? false);
                $this->apply_technical_note = (bool) ($this->purchaseOrder->apply_technical_note ?? false);
                $this->etd_initial_validated = (bool) ($this->purchaseOrder->etd_initial_validated ?? false);

                $this->date_booking_request = optional($this->purchaseOrder->date_booking_request)?->format('Y-m-d');
                $this->date_booking_authorized = optional($this->purchaseOrder->date_booking_authorized)?->format('Y-m-d');
                $this->date_theorical_load = optional($this->purchaseOrder->date_theorical_load)?->format('Y-m-d');
                $this->date_variable_date = optional($this->purchaseOrder->date_variable_date)?->format('Y-m-d');
                $this->carga_lista_validada = $this->purchaseOrder->carga_lista_validada ?? false;
                $this->date_received = optional($this->purchaseOrder->date_received)?->format('Y-m-d');
                $this->date_etd_initial = optional($this->purchaseOrder->date_etd_initial)?->format('Y-m-d');
                $this->inspection_date = optional($this->purchaseOrder->inspection_date)?->format('Y-m-d');
                $this->vgm_cut_date = optional($this->purchaseOrder->vgm_cut_date)?->format('Y-m-d');
                $this->balance_payment_date = optional($this->purchaseOrder->balance_payment_date)?->format('Y-m-d');
                $this->local_charges_payment_date = optional($this->purchaseOrder->local_charges_payment_date)?->format('Y-m-d');
                $this->bonded_warehouse_enter = optional($this->purchaseOrder->bonded_warehouse_enter)?->format('Y-m-d');
                $this->bonded_warehouse_exit = optional($this->purchaseOrder->bonded_warehouse_exit)?->format('Y-m-d');
                $this->receipt_note_date = optional($this->purchaseOrder->receipt_note_date)?->format('Y-m-d');
                $this->estimated_dc_availability_date = optional($this->purchaseOrder->estimated_dc_availability_date)?->format('Y-m-d');

                $this->logistics_incoterm = $this->purchaseOrder->logistics_incoterm;
                $this->reason = $this->purchaseOrder->reason;
                $this->category = $this->purchaseOrder->category;
                $this->forwarder_name = $this->purchaseOrder->forwarder_name;
                $this->cargo_invoice_number = $this->purchaseOrder->cargo_invoice_number;
                $this->tariff_type = $this->purchaseOrder->tariff_type;
                $this->route_label = $this->purchaseOrder->route_label;
                // Asegurar que el valor guardado esté en el array de opciones
                if ($this->route_label && ! isset($this->routeLabelArray[$this->route_label])) {
                    $this->routeLabelArray[$this->route_label] = $this->route_label;
                }
                $this->retail_group = $this->purchaseOrder->retail_group;
                $this->customer_type = $this->purchaseOrder->customer_type;
                $this->trading_company = $this->purchaseOrder->trading_company;
                $this->service_provider = $this->purchaseOrder->service_provider;
                $this->customs_dua = $this->purchaseOrder->customs_dua;
                $this->invoice = $this->purchaseOrder->invoice;
                $this->factura_merca = $this->purchaseOrder->factura_merca;
                $this->case_number_file = $this->purchaseOrder->case_number_file;
                $this->receipt_note = $this->purchaseOrder->receipt_note;
                $this->visibility_notes = $this->purchaseOrder->visibility_notes;

                $this->departure_port = $this->purchaseOrder->departure_port;
                $this->arrival_port = $this->purchaseOrder->arrival_port;

                $this->arrival_status = $this->purchaseOrder->arrival_status;
                $this->delay_days = $this->purchaseOrder->delay_days;

                // Costos
                $this->po_amount = (float) $this->purchaseOrder->po_amount;
                $this->Invoice_amount = (float) $this->purchaseOrder->Invoice_amount;
                $this->freight_amount = (float) $this->purchaseOrder->freight_amount;
                $this->total_amount = (float) $this->purchaseOrder->total_amount;

                // Métricas / contadores
                $this->container_free_days = $this->purchaseOrder->container_free_days;
                $this->etd_dates_difference = $this->purchaseOrder->etd_dates_difference;
                $this->eta_dates_difference = $this->purchaseOrder->eta_dates_difference;

                $this->date_eta_initial = optional($this->purchaseOrder->date_eta_initial)?->format('Y-m-d');
                // ETA Variable se guarda en date_eta, no en date_eta_updated (que no existe en BD)
                // Cargar desde date_eta en lugar de date_eta_updated
                $this->date_eta_updated = optional($this->purchaseOrder->date_eta)?->format('Y-m-d');

                // Campos extras que faltaban
                $this->cbm = $this->purchaseOrder->cbm;
                $this->consolidator_name = $this->purchaseOrder->consolidator_name;
                // No sobrescribir vendor_number si la PO lo tiene vacío (mantener el asignado desde vendor en mount)
                $this->vendor_number = $this->purchaseOrder->vendor_number ?? $this->vendor_number;

                $this->dif_load_date = $this->purchaseOrder->dif_load_date;
                $this->emision_date_po = optional($this->purchaseOrder->emision_date_po)?->format('Y-m-d');
                $this->forwader_date = optional($this->purchaseOrder->forwader_date)?->format('Y-m-d');

                // Recalcular diferencias derivadas
                $this->computeDateDiffs();

                // Cargar opciones de maestros usando el trading_company guardado
                if ($this->trading_company) {
                    $this->loadMaestrosOptions();
                }

                // Cargar productos
                $this->orderProducts = [];
                foreach ($this->purchaseOrder->products as $product) {
                    $this->orderProducts[] = [
                        'id' => $product->id,
                        'material_id' => $product->material_id,
                        'short_text' => $product->short_text,
                        'price_per_unit' => $product->pivot->unit_price,
                        'quantity' => $product->pivot->quantity,
                        'subtotal' => $product->pivot->unit_price * $product->pivot->quantity,
                    ];
                }
            }
        } else {
            // Inicializar el array de productos vacío
            $this->orderProducts = [];

            // Catálogos maestros: sin trading_company aún, usar empresa del usuario (OLO-009 / OLO-020)
            $this->loadMaestrosOptions();

            // Generar un número de orden único
            // $this->generateUniqueOrderNumber();
        }
    }

    /**
     * Get MaestrosApiService instance
     */
    protected function getMaestrosApiService(): MaestrosApiService
    {
        return app(MaestrosApiService::class);
    }

    /**
     * Valor de compañía/cliente para consultar maestros: trading_company si tiene ≥2 caracteres; si no, nombre de la empresa del usuario.
     */
    protected function resolveMaestrosTradingCompanyForApi(): string
    {
        $trading = trim((string) ($this->trading_company ?? ''));
        if (strlen($trading) >= 2) {
            return $trading;
        }
        $user = auth()->user();
        if ($user && $user->company_id) {
            $name = Company::query()->whereKey($user->company_id)->value('name');

            return trim((string) ($name ?? ''));
        }

        return '';
    }

    protected function loadHubs()
    {
        $hubs = Hub::orderBy('name')->get();
        $this->hubsArray = $hubs->pluck('name', 'id')->toArray();
    }

    public function loadBillTo()
    {
        $billTo = BillTo::orderBy('name')->get();
        $this->billToArray = $billTo->pluck('name', 'id')->toArray();
    }

    /**
     * Load maestros options from API
     * Al editar, asegura que los valores guardados en DB estén en los arrays
     * Usa el valor del campo trading_company para filtrar los datos
     */
    protected function loadMaestrosOptions()
    {
        $tradingCompanyValue = $this->resolveMaestrosTradingCompanyForApi();
        if ($tradingCompanyValue === '') {
            $this->departurePortArray = [];
            $this->arrivalPortArray = [];
            $this->shippingLineArray = [];
            $this->containerTypeArray = [];
            $this->serviceProviderArray = [];
            $this->transportTypeArray = [];
            $this->rateTypeArray = [];

            return;
        }

        try {
            $apiService = $this->getMaestrosApiService();

            // Parámetros comunes para las llamadas a la API usando el valor de trading_company
            $apiParams = [
                'company' => $tradingCompanyValue,
                'trading_company' => $tradingCompanyValue,
                'active' => 'true',
                'per_page' => 1000, // Obtener todos los registros activos
            ];

            // Cargar Puertos (para origen y destino)
            $portsResponse = $apiService->getPorts($apiParams);
            $portsArray = $this->processApiResponse($portsResponse, 'name', 'name');
            $this->departurePortArray = $portsArray;
            $this->arrivalPortArray = $portsArray;

            // Al editar, asegurar que los valores guardados estén en los arrays
            if ($this->id) {
                if ($this->departure_port && ! isset($this->departurePortArray[$this->departure_port])) {
                    $this->departurePortArray[$this->departure_port] = $this->departure_port;
                }
                if ($this->arrival_port && ! isset($this->arrivalPortArray[$this->arrival_port])) {
                    $this->arrivalPortArray[$this->arrival_port] = $this->arrival_port;
                }
            }

            // Cargar Shipping Lines
            $shippingLinesResponse = $apiService->getShippingLines($apiParams);
            $this->shippingLineArray = $this->processApiResponse($shippingLinesResponse, 'name', 'name');
            if ($this->id && $this->shipping_line && ! isset($this->shippingLineArray[$this->shipping_line])) {
                $this->shippingLineArray[$this->shipping_line] = $this->shipping_line;
            }

            // Cargar Container Types
            $containerTypesResponse = $apiService->getContainerTypes($apiParams);
            $this->containerTypeArray = $this->processApiResponse($containerTypesResponse, 'name', 'name');
            if ($this->id && $this->container_type && ! isset($this->containerTypeArray[$this->container_type])) {
                $this->containerTypeArray[$this->container_type] = $this->container_type;
            }

            // Cargar Service Providers
            $serviceProvidersResponse = $apiService->getServiceProviders($apiParams);
            $this->serviceProviderArray = $this->processApiResponse($serviceProvidersResponse, 'name', 'name');
            if ($this->id && $this->service_provider && ! isset($this->serviceProviderArray[$this->service_provider])) {
                $this->serviceProviderArray[$this->service_provider] = $this->service_provider;
            }

            // Cargar Transport Types
            $transportTypesResponse = $apiService->getTransportTypes($apiParams);
            $this->transportTypeArray = $this->processApiResponse($transportTypesResponse, 'name', 'name');
            if ($this->id && $this->mode && ! isset($this->transportTypeArray[$this->mode])) {
                $this->transportTypeArray[$this->mode] = $this->mode;
            }

            // Cargar Rate Types
            $rateTypesResponse = $apiService->getRateTypes($apiParams);
            $this->rateTypeArray = $this->processApiResponse($rateTypesResponse, 'name', 'name');
            if ($this->id && $this->tariff_type && ! isset($this->rateTypeArray[$this->tariff_type])) {
                $this->rateTypeArray[$this->tariff_type] = $this->tariff_type;
            }

        } catch (\Exception $e) {
            \Log::error('Error loading maestros options in CreatePucharseOrder', [
                'error' => $e->getMessage(),
                'trading_company' => $tradingCompanyValue,
            ]);
        }

        // Agregar mensajes de "no hay datos" si los arrays están vacíos
        // Esto se hace fuera del try-catch para garantizar que siempre se ejecute
        if (empty($this->departurePortArray)) {
            $this->departurePortArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
        }
        if (empty($this->arrivalPortArray)) {
            $this->arrivalPortArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
        }
        if (empty($this->shippingLineArray)) {
            $this->shippingLineArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
        }
        if (empty($this->containerTypeArray)) {
            $this->containerTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
        }
        if (empty($this->serviceProviderArray)) {
            $this->serviceProviderArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
        }
        if (empty($this->transportTypeArray)) {
            $this->transportTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
        }
        if (empty($this->rateTypeArray)) {
            $this->rateTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
        }
    }

    /**
     * Process API response and convert to array format for dropdowns
     * Los resultados se ordenan alfabéticamente
     *
     * @param  array|null  $response
     * @param  string  $keyField  Field to use as array key
     * @param  string  $valueField  Field to use as array value
     * @return array
     */
    protected function processApiResponse($response, $keyField = 'name', $valueField = 'name')
    {
        if (! $response || ! isset($response['data'])) {
            return [];
        }

        $result = [];
        foreach ($response['data'] as $item) {
            $key = $item[$keyField] ?? $item['name'] ?? '';
            $value = $item[$valueField] ?? $item['name'] ?? '';
            if ($key && $value) {
                $result[$key] = $value;
            }
        }

        // Ordenar alfabéticamente por valor
        asort($result);

        return $result;
    }

    /**
     * Filtrar valores especiales '__no_data__' para evitar guardarlos en la base de datos
     * Convierte '__no_data__' o strings vacíos a null
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function filterNoDataValue($value)
    {
        if ($value === '__no_data__' || $value === '') {
            return null;
        }

        return $value;
    }

    public function generateUniqueOrderNumber()
    {
        $companyId = auth()->user()->company_id ?? 1;

        // Formato: PO-YYYYMMDD-XXXX donde XXXX es un número secuencial
        $prefix = 'PO-'.date('Ymd').'-';

        // Obtener el último número de orden con este prefijo para esta compañía
        $lastOrder = \App\Models\PurchaseOrder::where('company_id', $companyId)
            ->where('order_number', 'like', $prefix.'%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            // Extraer el número secuencial y aumentarlo en 1
            $lastNumber = substr($lastOrder->order_number, strlen($prefix));
            $newNumber = intval($lastNumber) + 1;
            $this->order_number = $prefix.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } else {
            // Si no hay órdenes previas con este prefijo, empezar con 0001
            $this->order_number = $prefix.'0001';
        }

        // Verificar que el número generado sea único (precaución extra)
        $attempts = 0;
        while (\App\Models\PurchaseOrder::where('company_id', $companyId)
            ->where('order_number', $this->order_number)
            ->exists() && $attempts < 100) {

            $attempts++;
            $lastNumber = substr($this->order_number, strlen($prefix));
            $newNumber = intval($lastNumber) + 1;
            $this->order_number = $prefix.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }

        // Clear any previous validation errors for order_number
        $this->resetErrorBag('order_number');
    }

    public function onVendorSelected()
    {
        if ($this->vendor_id) {
            $vendor = Vendor::where('vendo_code', $this->vendor_id)->first();
            if ($vendor) {
                $this->vendor_direccion = $vendor->vendor_direccion ?? null;
                $this->vendor_pais = $vendor->vendor_pais ?? null;
                $this->vendor_telefono = $vendor->vendor_telefono ?? null;
                $this->vendor_number = $vendor->vendo_code ?? ''; // Asignar el código del proveedor o string vacío si es null
            } else {
                // Si no se encuentra el vendor, limpiar los campos
                $this->vendor_direccion = null;
                $this->vendor_pais = null;
                $this->vendor_telefono = null;
                $this->vendor_number = '';
            }
        } else {
            // Si no hay vendor_id, limpiar los campos
            $this->vendor_direccion = null;
            $this->vendor_pais = null;
            $this->vendor_telefono = null;
            $this->vendor_number = '';
        }
    }

    public function onShipToSelected()
    {
        if ($this->ship_to_id) {
            $shipTo = ShipTo::find($this->ship_to_id);
            if ($shipTo) {
                $this->ship_to_nombre = $shipTo->name;
                $this->ship_to_direccion = $shipTo->ship_to_direccion;
                $this->ship_to_pais = $shipTo->ship_to_pais;
                $this->ship_to_telefono = $shipTo->ship_to_telefono;
            }
        }
    }

    public function onBillToSelected()
    {
        if ($this->bill_to_id) {
            $billTo = BillTo::find($this->bill_to_id);
            if ($billTo) {
                $this->bill_to_nombre = $billTo->name;
                $this->bill_to_direccion = $billTo->address;
                $this->bill_to_pais = $billTo->country;
                $this->bill_to_telefono = $billTo->phone;
            }
        }
    }

    public function updatedVendorId()
    {
        $this->onVendorSelected();
        // Forzar actualización del componente para asegurar que los cambios se reflejen inmediatamente
        $this->dispatch('vendor-changed');
    }

    public function updatedShipToId()
    {
        $this->onShipToSelected();
    }

    public function updatedBillToId()
    {
        $this->onBillToSelected();
    }

    /**
     * Listener para cuando cambia trading_company
     * Recarga las opciones de los demás dropdowns
     * Muestra una alerta mientras carga los datos de la API
     */
    public function updatedTradingCompany()
    {
        $tradingCompanyValue = trim($this->trading_company ?? '');

        if (strlen($tradingCompanyValue) < 2) {
            $this->loadMaestrosOptions();

            return;
        }

        // Cargar datos de la API
        $this->loadMaestrosFromApi($tradingCompanyValue);
    }

    /**
     * Carga los datos de maestros desde la API
     * Método separado para poder mostrar las alertas correctamente
     */
    public function loadMaestrosFromApi($tradingCompanyValue)
    {
        $this->isLoadingMaestros = true;

        try {
            // Cargar las opciones de la API
            $this->loadMaestrosOptions();

            // Contar cuántos datos se cargaron
            $totalItems = count($this->departurePortArray) + count($this->shippingLineArray) +
                          count($this->containerTypeArray) + count($this->serviceProviderArray) +
                          count($this->transportTypeArray) + count($this->rateTypeArray);

            if ($totalItems > 0) {
                // Mostrar mensaje de éxito usando JavaScript
                $this->js("
                    window.dispatchEvent(new CustomEvent('show-success', {
                        detail: 'Datos cargados correctamente para: {$tradingCompanyValue} ({$totalItems} opciones encontradas)'
                    }));
                ");
            } else {
                // No se encontraron datos
                $this->js("
                    window.dispatchEvent(new CustomEvent('show-error', {
                        detail: 'No se encontraron datos para el cliente: {$tradingCompanyValue}'
                    }));
                ");
            }
        } catch (\Exception $e) {
            \Log::error('Error al cargar maestros: '.$e->getMessage());
            $errorMessage = addslashes($e->getMessage());
            $this->js("
                window.dispatchEvent(new CustomEvent('show-error', {
                    detail: 'Error al cargar datos: {$errorMessage}'
                }));
            ");
        } finally {
            $this->isLoadingMaestros = false;
        }
    }

    public function searchProducts()
    {
        if (strlen($this->searchTerm) >= 2) {
            $this->searchResults = Product::where('material_id', 'like', '%'.$this->searchTerm.'%')
                ->orWhere('short_text', 'like', '%'.$this->searchTerm.'%')
                ->take(5)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->searchResults = [];
    }

    public function addProduct()
    {
        if ($this->selectedProduct) {
            // Verificar si el producto ya está en la lista
            $existingIndex = null;
            foreach ($this->orderProducts as $index => $product) {
                if ($product['id'] == $this->selectedProduct->id) {
                    $existingIndex = $index;
                    break;
                }
            }

            if ($existingIndex !== null) {
                // Si el producto ya existe, actualizar la cantidad
                $this->orderProducts[$existingIndex]['quantity'] += $this->quantity;
                $this->orderProducts[$existingIndex]['subtotal'] =
                    $this->orderProducts[$existingIndex]['quantity'] * $this->orderProducts[$existingIndex]['price_per_unit'];
            } else {
                // Si es un nuevo producto, agregarlo al array
                $this->orderProducts[] = [
                    'id' => $this->selectedProduct->id,
                    'material_id' => $this->selectedProduct->material_id,
                    'short_text' => $this->selectedProduct->short_text,
                    'price_per_unit' => $this->selectedProduct->price_per_unit,
                    'quantity' => $this->quantity,
                    'subtotal' => $this->selectedProduct->price_per_unit * $this->quantity,
                ];
            }

            // Limpiar la selección
            $this->selectedProduct = null;
            $this->searchTerm = '';
            $this->quantity = 1;

            // Recalcular totales
            $this->calculateTotals();
        }
    }

    public function removeProduct($index)
    {
        // Eliminar el producto del array
        unset($this->orderProducts[$index]);
        $this->orderProducts = array_values($this->orderProducts); // Reindexar el array

        // Recalcular totales
        $this->calculateTotals();
    }

    public function updateQuantity($index, $newQuantity)
    {
        // Actualizar la cantidad y el subtotal
        $this->orderProducts[$index]['quantity'] = $newQuantity;
        $this->orderProducts[$index]['subtotal'] =
            $this->orderProducts[$index]['quantity'] * $this->orderProducts[$index]['price_per_unit'];

        // Recalcular totales
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // Neto basado en productos
        $this->net_total = 0;
        foreach ($this->orderProducts as $product) {
            $this->net_total += floatval($product['subtotal']);
        }

        // Costo adicional = OFR real + flete + transportes + nacionalización + otros gastos
        $this->additional_cost =
            floatval($this->cost_ofr_real) +
            floatval($this->freight_amount) +
            floatval($this->ground_transport_cost_1) +
            floatval($this->ground_transport_cost_2) +
            floatval($this->cost_nationalization) +
            floatval($this->other_expenses);

        // Total final
        $this->total = floatval($this->net_total)
            + floatval($this->additional_cost)
            + floatval($this->insurance_cost);

        // Ahorros (mantiene tu lógica)
        $this->calculateSavings();

        // Diferencias de fechas derivadas (por si cambió algo)
        $this->computeDateDiffs();
    }

    public function updatedAdditionalCost()
    {
        $this->calculateTotals();
    }

    public function updatedInsuranceCost()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    protected function prepareDateFields()
    {
        $dateFields = [
            'order_date',
            'date_required_in_destination',
            'date_planned_pickup',
            'date_actual_pickup',
            'date_estimated_hub_arrival',
            'date_actual_hub_arrival',
            'date_etd',
            'date_atd',
            'date_eta',
            'date_ata',
            'date_consolidation',
            'release_date',
        ];

        foreach ($dateFields as $field) {
            if (empty($this->$field)) {
                $this->$field = null;
            }
        }
    }

    public function createPurchaseOrder()
    {
        \Log::info('=== INICIO createPurchaseOrder ===', [
            'id' => $this->id,
            'actual_hub_id' => $this->actual_hub_id,
            'planned_hub_id' => $this->planned_hub_id,
            'order_number' => $this->order_number,
            'user_id' => auth()->id(),
        ]);

        // Get company ID for validation
        $companyId = auth()->user()->company_id ?? 1;

        // Normalizaciones previas a guardar
        $this->container_number = ContainerNumber::normalize($this->container_number);
        $this->computeDateDiffs();

        try {
            // Para validar incoterms
            $allowedIncoterms = implode(',', array_keys($this->tiposIncotermArray));

            // Validación básica
            $this->validate([
                'order_number' => [
                    'required',
                    'unique:purchase_orders,order_number,NULL,id,company_id,'.$companyId,
                ],
                'trading_company' => 'required|string',
                'incoterms' => "nullable|string|in:$allowedIncoterms",
                'logistics_incoterm' => "required|string|in:$allowedIncoterms",
                'price_incoterm' => "required|string|in:$allowedIncoterms",
                'vendor_id' => 'required',
                'currency' => 'required|string',
                'category' => 'nullable|string',
                'factory_proforma_number' => 'nullable|string',
                'container_number' => ['nullable', ContainerNumber::VALIDATION_RULE],
                'route_label' => 'required|string',
                'date_theorical_load' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        $emisionDate = $this->emision_date_po;

                        if ($emisionDate && $value < $emisionDate) {
                            $fail('La fecha de carga lista teórica no puede ser anterior a la fecha de emisión de la PO ('.formatDate($emisionDate).')');
                        }
                    },
                ],
                'carga_lista_validada' => 'nullable|boolean',
                'reason' => 'nullable|string',
            ], [
                'order_number.required' => 'El número de orden es requerido',
                'order_number.unique' => 'Este número de orden ya existe. Por favor, use un número diferente.',
                'trading_company.required' => 'El campo Cliente es requerido',
                'logistics_incoterm.required' => 'El incoterm logístico es obligatorio.',
                'logistics_incoterm.in' => 'El incoterm logístico seleccionado no es válido.',
                'price_incoterm.required' => 'El incoterm de precio es requerido',
                'vendor_id.required' => 'El vendor es requerido',
                'currency.required' => 'La moneda es requerida',
                'route_label.required' => 'La ruta es requerida',
                'date_theorical_load.required' => 'La fecha de Carga Lista Teorica es requerida',
                'container_number.regex' => 'El número de contenedor debe tener 4 letras seguidas de 7 dígitos. Ejemplo: ABCD1234567.',
            ]);

            try {
                // Usar transacción para asegurar integridad
                \DB::beginTransaction();

                // Resolver o crear vendor (vendor_id en form = vendo_code; DB espera vendors.id)
                $vendor = null;
                if ($this->vendor_id) {
                    $vendor = Vendor::where('vendo_code', $this->vendor_id)->first();
                    if (! $vendor) {
                        $vendor = Vendor::create([
                            'company_id' => $companyId,
                            'vendo_code' => (string) $this->vendor_id,
                            'name' => 'Proveedor '.$this->vendor_id,
                            'status' => 'active',
                        ]);
                    }
                }

                // Preparar los datos para la orden de compra
                if (! empty($this->date_ata)) {
                    $this->calculateDeliveryCompliance();
                }

                $poData = [
                    'company_id' => $companyId,
                    'order_number' => $this->order_number,
                    'status' => $this->id ? $this->status : 'draft',
                    'kanban_status_id' => $this->id ? $this->kanban_status_id : 2,
                    'notes' => $this->notes,
                    'vendor_id' => $vendor?->id,
                    'ship_to_id' => $this->ship_to_id,
                    'bill_to_id' => $this->bill_to_id,
                    'order_date' => $this->order_date,
                    'currency' => $this->currency,
                    'incoterms' => $this->incoterms,
                    'payment_terms' => $this->payment_terms,
                    'net_total' => $this->net_total,
                    'additional_cost' => $this->additional_cost,
                    'total' => $this->total,
                    'insurance_cost' => $this->insurance_cost,
                    'length' => $this->largo,
                    'width' => $this->ancho,
                    'height' => $this->alto,
                    'volume' => $this->volumen,
                    'weight_kg' => $this->peso_kg,
                    'weight_lb' => $this->peso_lb,
                    'planned_hub_id' => $this->planned_hub_id,
                    'actual_hub_id' => $this->actual_hub_id,
                    'date_required_in_destination' => $this->date_required_in_destination,
                    'date_planned_pickup' => $this->date_planned_pickup,
                    'date_actual_pickup' => $this->date_actual_pickup,
                    'date_estimated_hub_arrival' => $this->date_estimated_hub_arrival,
                    'date_actual_hub_arrival' => $this->date_actual_hub_arrival,
                    'date_etd' => $this->date_etd,
                    'date_atd' => $this->date_atd,
                    'date_eta' => $this->date_eta,
                    'date_ata' => $this->date_ata,
                    'date_consolidation' => $this->date_consolidation,
                    'release_date' => $this->release_date,
                    'material_type' => json_encode($this->material_type),
                    'ensurence_type' => $this->ensurence_type,
                    'mode' => $this->filterNoDataValue($this->mode),
                    'tracking_id' => $this->tracking_id,
                    'pallet_quantity' => $this->pallet_quantity,
                    'pallet_quantity_real' => $this->pallet_quantity_real,
                    'bill_of_lading' => $this->bill_of_lading,
                    'ground_transport_cost_1' => $this->ground_transport_cost_1,
                    'ground_transport_cost_2' => $this->ground_transport_cost_2,
                    'cost_nationalization' => $this->cost_nationalization,
                    'cost_ofr_estimated' => $this->cost_ofr_estimated,
                    'cost_ofr_real' => $this->cost_ofr_real,
                    'estimated_pallet_cost' => $this->estimated_pallet_cost,
                    'real_cost_estimated_po' => $this->real_cost_estimated_po,
                    'real_cost_real_po' => $this->real_cost_real_po,
                    'other_costs' => $this->other_costs,
                    'other_expenses' => $this->other_expenses,
                    'variable_calculare_weight' => $this->variable_calculare_weight,
                    'savings_ofr_fcl' => $this->savings_ofr_fcl,
                    'saving_pickup' => $this->saving_pickup,
                    'saving_executed' => $this->saving_executed,
                    'saving_not_executed' => $this->saving_not_executed,
                    'comments' => $this->comments,
                    'length_cm' => $this->length_cm,
                    'width_cm' => $this->width_cm,
                    'height_cm' => $this->height_cm,

                    // === Campos nuevos para OLO ===
                    'factory_proforma_number' => $this->factory_proforma_number,
                    'mbl_number' => $this->mbl_number,
                    'container_type' => $this->filterNoDataValue($this->container_type),
                    'container_number' => $this->container_number,
                    'shipping_line' => $this->filterNoDataValue($this->shipping_line),

                    'is_dropship' => (bool) ($this->is_dropship ?? false),
                    'applies_tlc' => (bool) ($this->applies_tlc ?? false),
                    'applies_af' => (bool) ($this->applies_af ?? false),

                    'date_booking_request' => $this->date_booking_request,
                    'date_booking_authorized' => $this->date_booking_authorized,
                    'date_theorical_load' => $this->date_theorical_load,
                    'date_variable_date' => $this->date_variable_date,
                    'carga_lista_validada' => $this->carga_lista_validada ?? false,
                    'date_received' => $this->date_received,

                    'logistics_incoterm' => $this->logistics_incoterm,
                    'price_incoterm' => $this->price_incoterm,
                    'reason' => $this->reason,
                    'category' => $this->category,
                    'forwarder_name' => $this->forwarder_name,
                    'cargo_invoice_number' => $this->cargo_invoice_number,
                    'tariff_type' => $this->filterNoDataValue($this->tariff_type),
                    'route_label' => $this->route_label,

                    'departure_port' => $this->filterNoDataValue($this->departure_port),
                    'arrival_port' => $this->filterNoDataValue($this->arrival_port),

                    'arrival_status' => $this->arrival_status,
                    'delay_days' => $this->delay_days,
                    'tracking_not_applicable' => (bool) ($this->tracking_not_applicable ?? false),

                    'date_eta_initial' => $this->date_eta_initial,
                    'date_eta_updated' => $this->date_eta_updated,

                    'port_of_loading_validated' => (bool) ($this->port_of_loading_validated ?? false),
                    'has_facture_merca' => (bool) ($this->has_facture_merca ?? false),
                    'used_rate_ok' => (bool) ($this->used_rate_ok ?? false),
                    'uses_bonded_warehouse' => (bool) ($this->uses_bonded_warehouse ?? false),
                    'apply_technical_note' => (bool) ($this->apply_technical_note ?? false),
                    'etd_initial_validated' => (bool) ($this->etd_initial_validated ?? false),

                    'date_etd_initial' => $this->date_etd_initial,
                    'inspection_date' => $this->inspection_date,
                    'vgm_cut_date' => $this->vgm_cut_date,
                    'balance_payment_date' => $this->balance_payment_date,
                    'local_charges_payment_date' => $this->local_charges_payment_date,
                    'bonded_warehouse_enter' => $this->bonded_warehouse_enter,
                    'bonded_warehouse_exit' => $this->bonded_warehouse_exit,
                    'receipt_note_date' => $this->receipt_note_date,
                    'estimated_dc_availability_date' => $this->estimated_dc_availability_date,

                    'retail_group' => $this->retail_group,
                    'customer_type' => $this->customer_type,
                    'trading_company' => $this->trading_company,
                    'service_provider' => $this->filterNoDataValue($this->service_provider),
                    'customs_dua' => $this->customs_dua,
                    'invoice' => $this->invoice,
                    'factura_merca' => $this->factura_merca,
                    'case_number_file' => $this->case_number_file,
                    'receipt_note' => $this->receipt_note,
                    'visibility_notes' => $this->visibility_notes,

                    'po_amount' => $this->po_amount,
                    'Invoice_amount' => $this->Invoice_amount,
                    'freight_amount' => $this->freight_amount,
                    'total_amount' => $this->total_amount,

                    'container_free_days' => $this->container_free_days,
                    'etd_dates_difference' => $this->etd_dates_difference,
                    'eta_dates_difference' => $this->eta_dates_difference,
                    'date_invoice_received' => $this->date_invoice_received,
                    'date_vendor_document_received' => $this->date_vendor_document_received,

                    'cbm' => $this->cbm,
                    'consolidator_name' => $this->consolidator_name,
                    'vendor_number' => $this->vendor_number,
                    'dif_load_date' => $this->dif_load_date,
                    'emision_date_po' => $this->emision_date_po,
                    'forwader_date' => $this->forwader_date,

                ];
                // po_amount no está en PurchaseOrder::$fillable ni en la tabla; no enviarlo a create()
                unset($poData['po_amount']);

                if ($this->shouldLockTrackingNotApplicableFields()) {
                    $poData = $this->withoutTrackingNotApplicableLockedFields($poData);
                }

                // Filtrar valores nulos o vacíos para evitar errores
                // Mantener valores 0, 0.0, false, y strings vacíos que puedan ser necesarios
                $poData = array_filter($poData, function ($value) {
                    // Mantener valores numéricos (incluyendo 0), booleanos, y arrays
                    if (is_numeric($value) || is_bool($value) || is_array($value)) {
                        return true;
                    }

                    // Eliminar solo null y strings vacíos
                    return $value !== null && $value !== '';
                });

                \Log::info('Intentando crear PO con datos', [
                    'order_number' => $poData['order_number'] ?? 'N/A',
                    'vendor_id' => $poData['vendor_id'] ?? 'N/A',
                    'company_id' => $poData['company_id'] ?? 'N/A',
                    'campos_count' => count($poData),
                ]);

                // Crear nueva orden
                try {
                    $purchaseOrder = \App\Models\PurchaseOrder::create($poData);
                    \Log::info('Orden creada con datos básicos', ['id' => $purchaseOrder->id, 'order_number' => $purchaseOrder->order_number]);
                } catch (\Exception $createException) {
                    \Log::error('Error al crear PurchaseOrder', [
                        'error' => $createException->getMessage(),
                        'trace' => $createException->getTraceAsString(),
                        'data_keys' => array_keys($poData),
                        'order_number' => $poData['order_number'] ?? 'N/A',
                    ]);
                    throw $createException;
                }

                // Guardar el ID de la orden recién creada
                $this->id = $purchaseOrder->id;

                // Asegurar que el número de orden esté actualizado
                $this->order_number = $purchaseOrder->order_number;

                // Guardar los productos asociados a la orden
                foreach ($this->orderProducts as $product) {
                    $purchaseOrder->products()->attach($product['id'], [
                        'quantity' => $product['quantity'] ?? 0,
                        'unit_price' => $product['price_per_unit'] ?? 0,
                    ]);
                }
                \DB::commit();

                // Dispatch webhook event for created purchase order
                if (function_exists('dispatch_webhook')) {
                    try {
                        \Log::info('About to dispatch webhook for PO creation from Livewire', [
                            'po_id' => $purchaseOrder->id,
                            'order_number' => $purchaseOrder->order_number,
                        ]);

                        $purchaseOrder->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                        $freshPo = $purchaseOrder->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

                        // Convertir a array y asegurar que sea JSON serializable
                        $poData = $freshPo->toArray();
                        $poData = json_decode(json_encode($poData), true);

                        \Log::info('Calling dispatch_webhook from Livewire (create)', [
                            'po_id' => $purchaseOrder->id,
                            'has_data' => isset($poData['id']),
                        ]);

                        dispatch_webhook('purchase_order.created', [
                            'purchase_order_id' => $purchaseOrder->id,
                            'order_number' => $purchaseOrder->order_number,
                            'data' => $poData,
                        ]);

                        \Log::info('dispatch_webhook completed from Livewire (create)', [
                            'po_id' => $purchaseOrder->id,
                        ]);
                    } catch (\Throwable $e) {
                        \Log::error('Error in webhook dispatch from Livewire (create)', [
                            'po_id' => $purchaseOrder->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        // No lanzar la excepción para no interrumpir el flujo principal
                    }
                }

                // Check if actual hub is different from planned hub
                if ($this->actual_hub_id && $this->planned_hub_id && $this->actual_hub_id !== $this->planned_hub_id) {
                    \Log::info('Verificando diferencia de hubs', [
                        'actual_hub_id' => $this->actual_hub_id,
                        'planned_hub_id' => $this->planned_hub_id,
                        'order_number' => $this->order_number,
                    ]);

                    $actualHub = \App\Models\Hub::find($this->actual_hub_id);
                    $plannedHub = \App\Models\Hub::find($this->planned_hub_id);

                    if ($actualHub && $plannedHub) {
                        \Log::info('Hubs encontrados, enviando notificación', [
                            'actual_hub' => $actualHub->name,
                            'planned_hub' => $plannedHub->name,
                        ]);

                        try {
                            $notificationService = app(\App\Services\NotificationService::class);

                            // Create notification for all users
                            $notifications = $notificationService->notifyAll(
                                'po_hub_real',
                                'Hub Real Diferente',
                                "La orden de compra {$this->order_number} se creó con un hub real ({$actualHub->name}) diferente al hub planificado ({$plannedHub->name})",
                                [
                                    'order_number' => $this->order_number,
                                    'planned_hub' => $plannedHub->name,
                                    'actual_hub' => $actualHub->name,
                                    'order_id' => $purchaseOrder->id,
                                    'type' => 'hub_change',
                                ]
                            );

                            \Log::info('Notificación enviada correctamente', [
                                'notifications' => $notifications,
                                'type' => 'po_hub_real',
                            ]);

                            // Dispatch events to refresh notifications
                            $this->dispatch('notificationsUpdated');
                            $this->dispatch('refresh-notifications');
                            $this->dispatch('notification-received');
                        } catch (\Exception $e) {
                            \Log::error('Error al enviar notificación: '.$e->getMessage(), [
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                }

                // Dispatch success notification
                $this->dispatch('show-success', 'Orden de compra creada exitosamente con número: '.$this->order_number);
                $this->dispatch('open-modal', 'modal-purchase-order-created');

            } catch (\Illuminate\Validation\ValidationException $e) {
                \DB::rollBack();
                \Log::warning('ValidationException capturada en try interno', [
                    'errors' => $e->errors(),
                    'message' => $e->getMessage(),
                ]);
                // Re-lanzar la ValidationException para que Livewire la maneje automáticamente
                throw $e;
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Error en createPurchaseOrder (try interno): '.$e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                // Check if it's a duplicate key error
                if (strpos($e->getMessage(), 'duplicate key value violates unique constraint') !== false ||
                    strpos($e->getMessage(), 'SQLSTATE[23505]') !== false) {
                    $this->addError('order_number', 'Este número de orden ya existe. Por favor, use un número diferente.');
                    $this->dispatch('show-error', 'Este número de orden ya existe. Por favor, use un número diferente.');
                } else {
                    $errorMessage = 'Error al guardar la orden: '.$e->getMessage();
                    session()->flash('error', $errorMessage);
                    $this->dispatch('show-error', $errorMessage);
                }

                // No re-lanzar aquí, ya se manejó el error
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Error de validación capturado en catch externo', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            // Re-lanzar para que Livewire muestre automáticamente los errores en los campos
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error inesperado en createPurchaseOrder (catch externo): '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $errorMessage = 'Error inesperado al crear la orden: '.$e->getMessage();
            session()->flash('error', $errorMessage);
            $this->dispatch('show-error', $errorMessage);
        }
    }

    public function testSimpleSave()
    {
        \Log::info('Iniciando prueba de guardado simple');

        try {
            // Datos mínimos necesarios para crear una orden
            $minimalData = [
                'company_id' => auth()->user()->company_id ?? 1,
                'order_number' => $this->order_number ?? 'TEST-'.time(),
                'order_date' => now(),
                'status' => 'draft',
            ];

            \Log::info('Intentando guardar datos mínimos', $minimalData);

            // Intento 1: Usando Eloquent create
            $order = \App\Models\PurchaseOrder::create($minimalData);
            \Log::info('Orden creada con Eloquent', ['id' => $order->id]);

            session()->flash('message', 'Prueba de guardado exitosa. ID: '.$order->id);

            return redirect()->route('purchase-orders.index');
        } catch (\Exception $e) {
            \Log::error('Error en prueba de guardado simple: '.$e->getMessage());
            \Log::error($e->getTraceAsString());

            // Intento 2: Si falla Eloquent, intentar con una consulta SQL directa
            try {
                \Log::info('Intentando guardado con consulta SQL directa');
                $id = \DB::table('purchase_orders')->insertGetId($minimalData);
                \Log::info('Orden creada con SQL directo', ['id' => $id]);

                session()->flash('message', 'Prueba de guardado SQL exitosa. ID: '.$id);

                return redirect()->route('purchase-orders.index');
            } catch (\Exception $e2) {
                \Log::error('Error en prueba SQL: '.$e2->getMessage());
                session()->flash('error', 'Error en ambos métodos: '.$e->getMessage().' y '.$e2->getMessage());
            }
        }
    }

    public function updatePurchaseOrder($id)
    {
        try {
            $this->restoreEditLockedApiFields((int) $id);
            $this->restoreEditLockedTrackingDateFields((int) $id);

            // Red de seguridad: si estamos editando y date_theorical_load está vacío pero existe en BD, recargar desde la PO
            if ($this->id && empty($this->date_theorical_load)) {
                $po = $this->purchaseOrder ?? \App\Models\PurchaseOrder::find($id);
                if ($po?->date_theorical_load) {
                    $this->date_theorical_load = $po->date_theorical_load->format('Y-m-d');
                }
            }

            \Log::info('=== INICIO updatePurchaseOrder ===', [
                'id' => $id,
                'order_number' => $this->order_number,
                'date_theorical_load' => $this->date_theorical_load,
                'date_variable_date' => $this->date_variable_date,
                'date_variable_date_type' => gettype($this->date_variable_date),
                'date_variable_date_empty' => empty($this->date_variable_date),
                'carga_lista_validada' => $this->carga_lista_validada,
                'emision_date_po' => $this->emision_date_po,
                'forwader_date' => $this->forwader_date,
                'date_booking_request' => $this->date_booking_request,
                'date_booking_authorized' => $this->date_booking_authorized,
                'date_eta_updated' => $this->date_eta_updated,
                'date_eta' => $this->date_eta,
                'date_ata' => $this->date_ata,
                'date_eta_initial' => $this->date_eta_initial,
            ]);

            // Validación alineada con creación + fechas de carga lista
            $companyId = auth()->user()->company_id ?? 1;
            $allowedIncoterms = implode(',', array_keys($this->tiposIncotermArray));
            $this->container_number = ContainerNumber::normalize($this->container_number);

            $this->validate([
                'order_number' => [
                    'required',
                    'string',
                    'unique:purchase_orders,order_number,'.$id.',id,company_id,'.$companyId,
                ],
                'trading_company' => 'required|string',
                'incoterms' => "nullable|string|in:$allowedIncoterms",
                'logistics_incoterm' => "required|string|in:$allowedIncoterms",
                'price_incoterm' => "required|string|in:$allowedIncoterms",
                'vendor_id' => 'required',
                'currency' => 'required|string',
                'category' => 'nullable|string',
                'factory_proforma_number' => 'nullable|string',
                'container_number' => ['nullable', ContainerNumber::VALIDATION_RULE],
                'route_label' => 'required|string',
                'date_theorical_load' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        $emisionDate = $this->emision_date_po;

                        if ($emisionDate && $value < $emisionDate) {
                            $fail('La fecha de carga lista teórica no puede ser anterior a la fecha de emisión de la PO ('.formatDate($emisionDate).')');
                        }
                    },
                ],
                'date_variable_date' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $emisionDate = $this->emision_date_po;

                            if ($emisionDate && $value < $emisionDate) {
                                $fail('La fecha de carga lista variable no puede ser anterior a la fecha de emisión de la PO ('.formatDate($emisionDate).')');
                            }
                        }
                    },
                ],
                'carga_lista_validada' => 'nullable|boolean',
                'reason' => 'nullable|string',
            ], [
                'order_number.required' => 'El número de orden es requerido',
                'order_number.unique' => 'Este número de orden ya existe. Por favor, use un número diferente.',
                'trading_company.required' => 'El campo Cliente es requerido',
                'logistics_incoterm.required' => 'El incoterm logístico es obligatorio.',
                'logistics_incoterm.in' => 'El incoterm logístico seleccionado no es válido.',
                'price_incoterm.required' => 'El incoterm de precio es requerido',
                'vendor_id.required' => 'El vendor es requerido',
                'currency.required' => 'La moneda es requerida',
                'route_label.required' => 'La ruta es requerida',
                'date_theorical_load.required' => 'La fecha de Carga Lista Teorica es requerida',
                'container_number.regex' => 'El número de contenedor debe tener 4 letras seguidas de 7 dígitos. Ejemplo: ABCD1234567.',
            ]);

            $this->computeDateDiffs();

            // Resolver vendor_id: el form usa vendo_code, la BD espera vendors.id
            $vendor = null;
            if ($this->vendor_id) {
                $vendor = Vendor::where('vendo_code', $this->vendor_id)->first();
                if (! $vendor) {
                    $companyId = auth()->user()->company_id ?? 1;
                    $vendor = Vendor::create([
                        'company_id' => $companyId,
                        'vendo_code' => (string) $this->vendor_id,
                        'name' => 'Proveedor '.$this->vendor_id,
                        'status' => 'active',
                    ]);
                }
            }

            $poData = [
                'order_number' => $this->order_number,
                'status' => $this->id ? $this->status : 'draft',
                'notes' => $this->notes,
                'vendor_id' => $vendor?->id,
                'ship_to_id' => $this->ship_to_id,
                'bill_to_id' => $this->bill_to_id,
                'order_date' => $this->order_date,
                'currency' => $this->currency,
                'incoterms' => $this->incoterms,
                'payment_terms' => $this->payment_terms,
                'net_total' => $this->net_total,
                'additional_cost' => $this->additional_cost,
                'total' => $this->total,
                'insurance_cost' => $this->insurance_cost,
                'length' => $this->largo,
                'width' => $this->ancho,
                'height' => $this->alto,
                'volume' => $this->volumen,
                'weight_kg' => $this->peso_kg,
                'weight_lb' => $this->peso_lb,
                'planned_hub_id' => $this->planned_hub_id,
                'actual_hub_id' => $this->actual_hub_id,
                // Material type is now a JSON array
                'material_type' => json_encode($this->material_type),
                // Fechas
                'date_required_in_destination' => $this->date_required_in_destination,
                'date_planned_pickup' => $this->date_planned_pickup,
                'date_actual_pickup' => $this->date_actual_pickup,
                'date_estimated_hub_arrival' => $this->date_estimated_hub_arrival,
                'date_actual_hub_arrival' => $this->date_actual_hub_arrival,
                'date_etd' => $this->date_etd,
                'date_atd' => $this->date_atd,
                // date_eta se maneja más abajo (se sobrescribe con date_eta_updated si existe)
                'date_ata' => $this->date_ata,
                'date_consolidation' => $this->date_consolidation,
                'release_date' => $this->release_date,
                // Otros campos
                'ensurence_type' => $this->ensurence_type,
                'mode' => $this->filterNoDataValue($this->mode),
                'tracking_id' => $this->tracking_id,
                'pallet_quantity' => $this->pallet_quantity,
                'pallet_quantity_real' => $this->pallet_quantity_real,
                'bill_of_lading' => $this->bill_of_lading,
                'ground_transport_cost_1' => $this->ground_transport_cost_1,
                'ground_transport_cost_2' => $this->ground_transport_cost_2,
                'cost_nationalization' => $this->cost_nationalization,
                'cost_ofr_estimated' => $this->cost_ofr_estimated,
                'cost_ofr_real' => $this->cost_ofr_real,
                'estimated_pallet_cost' => $this->estimated_pallet_cost,
                'real_cost_estimated_po' => $this->real_cost_estimated_po,
                'real_cost_real_po' => $this->real_cost_real_po,
                'other_costs' => $this->other_costs,
                'other_expenses' => $this->other_expenses,
                'variable_calculare_weight' => $this->variable_calculare_weight,
                'savings_ofr_fcl' => $this->savings_ofr_fcl,
                'saving_pickup' => $this->saving_pickup,
                'saving_executed' => $this->saving_executed,
                'saving_not_executed' => $this->saving_not_executed,
                'comments' => $this->comments,
                'length_cm' => $this->length_cm,
                'width_cm' => $this->width_cm,
                'height_cm' => $this->height_cm,

                // === Campos nuevos para OLO ===
                'factory_proforma_number' => $this->factory_proforma_number,
                'mbl_number' => $this->mbl_number,
                'container_type' => $this->filterNoDataValue($this->container_type),
                'container_number' => $this->container_number,
                'shipping_line' => $this->filterNoDataValue($this->shipping_line),

                'is_dropship' => (bool) ($this->is_dropship ?? false),
                'applies_tlc' => (bool) ($this->applies_tlc ?? false),
                'applies_af' => (bool) ($this->applies_af ?? false),

                'date_booking_request' => ! empty($this->date_booking_request) ? $this->date_booking_request : null,
                'date_booking_authorized' => ! empty($this->date_booking_authorized) ? $this->date_booking_authorized : null,
                'date_theorical_load' => ! empty($this->date_theorical_load) ? $this->date_theorical_load : null,
                'date_variable_date' => ! empty($this->date_variable_date) ? $this->date_variable_date : null,
                'carga_lista_validada' => $this->carga_lista_validada ?? false,
                'date_received' => ! empty($this->date_received) ? $this->date_received : null,

                'logistics_incoterm' => $this->logistics_incoterm,
                'price_incoterm' => $this->price_incoterm,
                'reason' => $this->reason,
                'category' => $this->category,
                'forwarder_name' => $this->forwarder_name,
                'cargo_invoice_number' => $this->cargo_invoice_number,
                'tariff_type' => $this->filterNoDataValue($this->tariff_type),
                'route_label' => $this->route_label,

                'departure_port' => $this->filterNoDataValue($this->departure_port),
                'arrival_port' => $this->filterNoDataValue($this->arrival_port),

                'arrival_status' => $this->arrival_status,
                'delay_days' => $this->delay_days,

                'date_eta_initial' => ! empty($this->date_eta_initial) ? $this->date_eta_initial : null,
                // ETA Variable se guarda en date_eta, no en date_eta_updated
                // Si date_eta_updated tiene valor, se usa para date_eta
                'date_eta' => ! empty($this->date_eta_updated) ? $this->date_eta_updated : (! empty($this->date_eta) ? $this->date_eta : null),

                'port_of_loading_validated' => (bool) ($this->port_of_loading_validated ?? false),
                'has_facture_merca' => (bool) ($this->has_facture_merca ?? false),
                'used_rate_ok' => (bool) ($this->used_rate_ok ?? false),
                'uses_bonded_warehouse' => (bool) ($this->uses_bonded_warehouse ?? false),
                'apply_technical_note' => (bool) ($this->apply_technical_note ?? false),
                'etd_initial_validated' => (bool) ($this->etd_initial_validated ?? false),
                'date_etd_initial' => ! empty($this->date_etd_initial) ? $this->date_etd_initial : null,
                'inspection_date' => ! empty($this->inspection_date) ? $this->inspection_date : null,
                'vgm_cut_date' => ! empty($this->vgm_cut_date) ? $this->vgm_cut_date : null,
                'balance_payment_date' => ! empty($this->balance_payment_date) ? $this->balance_payment_date : null,
                'local_charges_payment_date' => ! empty($this->local_charges_payment_date) ? $this->local_charges_payment_date : null,
                'bonded_warehouse_enter' => ! empty($this->bonded_warehouse_enter) ? $this->bonded_warehouse_enter : null,
                'bonded_warehouse_exit' => ! empty($this->bonded_warehouse_exit) ? $this->bonded_warehouse_exit : null,
                'receipt_note_date' => ! empty($this->receipt_note_date) ? $this->receipt_note_date : null,
                'estimated_dc_availability_date' => ! empty($this->estimated_dc_availability_date) ? $this->estimated_dc_availability_date : null,
                'retail_group' => $this->retail_group,
                'customer_type' => $this->customer_type,
                'trading_company' => $this->trading_company,
                'service_provider' => $this->filterNoDataValue($this->service_provider),
                'customs_dua' => $this->customs_dua,
                'invoice' => $this->invoice,
                'factura_merca' => $this->factura_merca,
                'case_number_file' => $this->case_number_file,
                'receipt_note' => $this->receipt_note,
                'visibility_notes' => $this->visibility_notes,
                'po_amount' => $this->po_amount,
                'Invoice_amount' => $this->Invoice_amount,
                'freight_amount' => $this->freight_amount,
                'total_amount' => $this->total_amount,
                'container_free_days' => $this->container_free_days,
                'etd_dates_difference' => $this->etd_dates_difference,
                'eta_dates_difference' => $this->eta_dates_difference,

                'date_invoice_received' => ! empty($this->date_invoice_received) ? $this->date_invoice_received : null,
                'date_vendor_document_received' => ! empty($this->date_vendor_document_received) ? $this->date_vendor_document_received : null,
                'cbm' => $this->cbm,
                'consolidator_name' => $this->consolidator_name,
                'vendor_number' => $this->vendor_number,
                'dif_load_date' => $this->dif_load_date !== '' ? $this->dif_load_date : null,
                'emision_date_po' => ! empty($this->emision_date_po) ? $this->emision_date_po : null,
                'forwader_date' => ! empty($this->forwader_date) ? $this->forwader_date : null,
            ];

            // Evitar overflow numérico en PostgreSQL (decimal 10,2 = max 99.999.999,99)
            $poData = $this->clampDecimalFieldsForDb($poData);

            try {
                // Usar transacción para asegurar integridad
                \DB::beginTransaction();

                $purchaseOrder = \App\Models\PurchaseOrder::findOrFail($id);

                if ($this->shouldRequestTrackingNotApplicable($purchaseOrder)) {
                    $trackingNotApplicableReason = trim((string) ($this->tracking_not_applicable_reason ?? ''));

                    if (blank($trackingNotApplicableReason)) {
                        \DB::rollBack();
                        $this->addError('tracking_not_applicable_reason', 'Debe indicar un motivo para solicitar No aplica tracking.');
                        $this->dispatch('show-error', 'Debe indicar un motivo para solicitar No aplica tracking.');

                        return [
                            'success' => false,
                            'message' => 'Debe indicar un motivo para solicitar No aplica tracking.',
                        ];
                    }

                    app(AuthorizationService::class)->createTrackingNotApplicableRequest(
                        $purchaseOrder,
                        $trackingNotApplicableReason
                    );

                    $this->tracking_not_applicable_reason = $trackingNotApplicableReason;
                    $this->tracking_not_applicable = false;
                    $this->trackingNotApplicableApproved = (bool) $purchaseOrder->tracking_not_applicable;
                    $this->trackingNotApplicablePending = true;
                    \DB::commit();

                    $this->dispatch('show-success', 'Solicitud de No aplica tracking enviada para aprobación.');

                    return [
                        'success' => true,
                        'message' => 'Solicitud de No aplica tracking enviada para aprobación.',
                        'authorization_pending' => true,
                    ];
                }

                $dateAtaChanged = array_key_exists('date_ata', $poData)
                    && $this->dateValueChanged($purchaseOrder->date_ata, $poData['date_ata']);

                if ($dateAtaChanged) {
                    $compliance = \App\Models\PurchaseOrder::calculateDeliveryCompliance(
                        $poData['date_eta_initial'] ?? $purchaseOrder->date_eta_initial,
                        $poData['date_ata'] ?? null
                    );

                    $poData['arrival_status'] = $compliance['arrival_status'];
                    $poData['delay_days'] = $compliance['delay_days'];
                    $this->arrival_status = $compliance['arrival_status'];
                    $this->delay_days = $compliance['delay_days'];
                } else {
                    unset($poData['arrival_status'], $poData['delay_days']);
                }

                $poData = $this->withoutEditLockedApiFields($poData);
                if ($this->shouldLockTrackingDateFields($purchaseOrder)) {
                    $poData = $this->withoutEditLockedTrackingDateFields($poData);
                }
                if ($this->shouldLockEtaInitial($purchaseOrder)) {
                    unset($poData['date_eta_initial']);
                }
                if ($this->shouldLockTrackingNotApplicableFields()) {
                    $poData = $this->withoutTrackingNotApplicableLockedFields($poData);
                }

                // Log antes de fill para ver el valor original
                \Log::info('Antes de fill - date_variable_date', [
                    'po_id' => $id,
                    'valor_original_bd' => $purchaseOrder->date_variable_date,
                    'valor_original_tipo' => gettype($purchaseOrder->date_variable_date),
                    'valor_en_poData' => $poData['date_variable_date'] ?? 'NO ESTÁ EN poData',
                    'valor_formulario' => $this->date_variable_date,
                ]);

                // Usar getDirty() para obtener solo campos que realmente cambiaron
                // Asignar valores primero sin guardar para que Eloquent detecte cambios
                $purchaseOrder->fill($poData);
                $dirtyFields = $purchaseOrder->getDirty();

                \Log::info('Después de fill - date_variable_date', [
                    'po_id' => $id,
                    'en_dirtyFields' => isset($dirtyFields['date_variable_date']),
                    'valor_en_dirtyFields' => $dirtyFields['date_variable_date'] ?? null,
                    'todos_dirtyFields' => array_keys($dirtyFields),
                ]);

                // Campos computados automáticamente que no deben enviarse como cambios del usuario
                // (se guardan en la BD pero no se reportan en el webhook como cambios del usuario)
                $computedFields = [
                    'arrival_status',
                    'delay_days',
                    'etd_dates_difference',
                    'eta_dates_difference',
                ];

                // Construir array de cambios solo con campos que realmente cambiaron
                // Filtrar campos donde el valor nuevo es igual al valor original (para evitar falsos positivos)
                $changes = [];
                $filteredOut = [];
                foreach ($dirtyFields as $field => $newValue) {
                    // Excluir campos computados del webhook
                    if (in_array($field, $computedFields)) {
                        $filteredOut[$field] = [
                            'old' => $purchaseOrder->getOriginal($field),
                            'new' => $newValue,
                            'reason' => 'computed_field',
                        ];

                        continue;
                    }

                    $oldValue = $purchaseOrder->getOriginal($field);

                    // Excluir cambios ruidosos (null→0 en montos/campos numéricos)
                    if (\App\Helpers\ChangeDescriptionHelper::isNoiseChange($field, $oldValue, $newValue)) {
                        $filteredOut[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue,
                            'reason' => 'noise_change',
                        ];

                        continue;
                    }

                    // Normalizar valores para comparación
                    $normalizedOld = $this->normalizeValueForComparison($oldValue);
                    $normalizedNew = $this->normalizeValueForComparison($newValue);

                    // Solo incluir si realmente cambió
                    if ($normalizedOld !== $normalizedNew) {
                        $changes[$field] = $newValue;
                    } else {
                        // Log campos que fueron filtrados (no cambiaron realmente)
                        $filteredOut[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue,
                            'normalized_old' => $normalizedOld,
                            'normalized_new' => $normalizedNew,
                        ];
                    }
                }

                // IMPORTANTE: Si después del filtrado no hay cambios reales, no guardar ni disparar webhook
                if (empty($changes)) {
                    \DB::rollBack();
                    \Log::info('No hay cambios reales después del filtrado, cancelando actualización', [
                        'po_id' => $id,
                        'dirty_fields_count' => count($dirtyFields),
                        'filtered_out_count' => count($filteredOut),
                    ]);

                    return [
                        'success' => true,
                        'message' => 'No se detectaron cambios reales',
                        'no_changes' => true,
                    ];
                }

                \Log::info('Filtrado de cambios en updatePurchaseOrder', [
                    'po_id' => $id,
                    'order_number' => $this->order_number,
                    'dirty_fields_count' => count($dirtyFields),
                    'dirty_fields' => array_keys($dirtyFields),
                    'real_changes_count' => count($changes),
                    'real_changes' => array_keys($changes),
                    'real_changes_values' => $changes,
                    'filtered_out_count' => count($filteredOut),
                    'filtered_out_fields' => array_keys($filteredOut),
                    'date_variable_date_dirty' => isset($dirtyFields['date_variable_date']),
                    'date_variable_date_in_changes' => isset($changes['date_variable_date']),
                    'date_variable_date_form_value' => $this->date_variable_date,
                    'date_variable_date_po_value' => $purchaseOrder->getOriginal('date_variable_date'),
                ]);

                \Log::info('Actualizando PO', [
                    'id' => $id,
                    'forwader_date' => $this->forwader_date,
                    'emision_date_po' => $this->emision_date_po,
                    'date_booking_request' => $this->date_booking_request,
                    'date_booking_authorized' => $this->date_booking_authorized,
                    'changed_fields' => array_keys($changes),
                    'date_eta_updated' => $this->date_eta_updated,
                    'date_eta' => $this->date_eta,
                    'date_ata' => $this->date_ata,
                    'date_eta_in_changes' => isset($changes['date_eta']),
                    'date_ata_in_changes' => isset($changes['date_ata']),
                ]);

                // Rehidratar el modelo antes de guardar para persistir solo los cambios reales
                // y evitar que "dirty" ruidoso termine enviando valores incompatibles a PostgreSQL.
                $purchaseOrder = \App\Models\PurchaseOrder::findOrFail($id);
                $purchaseOrder->fill($changes);

                try {
                    $purchaseOrder->save();
                } catch (\Exception $saveException) {
                    throw $saveException;
                }

                $freshPo = $purchaseOrder->fresh();
                \Log::info('PO actualizada exitosamente', [
                    'id' => $purchaseOrder->id,
                    'forwader_date_guardado' => $purchaseOrder->forwader_date?->format('Y-m-d'),
                    'date_eta_guardado' => $freshPo->date_eta?->format('Y-m-d'),
                    'date_ata_guardado' => $freshPo->date_ata?->format('Y-m-d'),
                    'date_eta_initial_guardado' => $freshPo->date_eta_initial?->format('Y-m-d'),
                    'date_variable_date_guardado' => $freshPo->date_variable_date?->format('Y-m-d'),
                    'date_variable_date_en_cambios' => isset($changes['date_variable_date']),
                    'date_variable_date_valor_en_cambios' => $changes['date_variable_date'] ?? null,
                    'date_variable_date_valor_formulario' => $this->date_variable_date,
                ]);

                // Eliminar productos existentes
                $purchaseOrder->products()->detach();

                // Guardar los productos actualizados asociados a la orden
                foreach ($this->orderProducts as $product) {
                    $purchaseOrder->products()->attach($product['id'], [
                        'quantity' => $product['quantity'] ?? 0,
                        'unit_price' => $product['price_per_unit'] ?? 0,
                    ]);
                }

                \DB::commit();

                // Dispatch webhook event for updated purchase order
                if (function_exists('dispatch_webhook')) {
                    try {
                        \Log::info('About to dispatch webhook for PO update from Livewire', [
                            'po_id' => $purchaseOrder->id,
                            'order_number' => $purchaseOrder->order_number,
                        ]);

                        $purchaseOrder->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                        $freshPo = $purchaseOrder->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

                        // Convertir a array y asegurar que sea JSON serializable
                        $poDataForWebhook = $freshPo->toArray();
                        $poDataForWebhook = json_decode(json_encode($poDataForWebhook), true);

                        // Los cambios ya fueron calculados antes del save() usando getDirty()
                        // $changes ya contiene solo los campos que realmente cambiaron

                        \Log::info('Changes for webhook', [
                            'changed_fields' => array_keys($changes),
                            'changes_count' => count($changes),
                        ]);

                        \Log::info('Calling dispatch_webhook from Livewire', [
                            'po_id' => $purchaseOrder->id,
                            'has_data' => isset($poDataForWebhook['id']),
                            'changes_count' => count($changes),
                            'changes_keys' => array_keys($changes),
                            'changes' => $changes,
                        ]);

                        dispatch_webhook('purchase_order.updated', [
                            'purchase_order_id' => $purchaseOrder->id,
                            'order_number' => $purchaseOrder->order_number,
                            'changes' => $changes, // Array de cambios filtrados
                            'data' => $poDataForWebhook,
                        ]);

                        \Log::info('dispatch_webhook completed from Livewire', [
                            'po_id' => $purchaseOrder->id,
                        ]);
                    } catch (\Throwable $e) {
                        \Log::error('Error in webhook dispatch from Livewire', [
                            'po_id' => $purchaseOrder->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        // No lanzar la excepción para no interrumpir el flujo principal
                    }
                } else {
                    \Log::warning('dispatch_webhook function is not available after Livewire PO update', [
                        'po_id' => $purchaseOrder->id,
                        'order_number' => $purchaseOrder->order_number,
                        'changes_count' => count($changes),
                        'changes_keys' => array_keys($changes),
                    ]);
                }

                // Check if actual hub is different from planned hub
                if ($this->actual_hub_id && $this->planned_hub_id && $this->actual_hub_id !== $this->planned_hub_id) {
                    $actualHub = Hub::find($this->actual_hub_id);
                    $plannedHub = Hub::find($this->planned_hub_id);

                    if ($actualHub && $plannedHub) {
                        $notificationService = app(\App\Services\NotificationService::class);
                        $notificationService->notifyAll(
                            'hub_changed',
                            'Cambio de Hub',
                            "La orden de compra {$this->order_number} tiene un hub real ({$actualHub->name}) diferente al hub planificado ({$plannedHub->name})",
                            [
                                'order_number' => $this->order_number,
                                'planned_hub' => $plannedHub->name,
                                'actual_hub' => $actualHub->name,
                                'order_id' => $purchaseOrder->id,
                            ]
                        );
                    }
                }

                // Dispatch success notification and open modal
                $this->dispatch('show-success', 'Orden de compra actualizada exitosamente con número: '.$this->order_number);
                $this->dispatch('open-modal', 'modal-purchase-order-created');

                // RETORNAR UN VALOR EXPLÍCITO PARA INDICAR ÉXITO
                return ['success' => true, 'message' => 'Orden actualizada exitosamente', 'id' => $purchaseOrder->id];

            } catch (\Illuminate\Validation\ValidationException $e) {
                \DB::rollBack();
                \Log::error('Error de validación en updatePurchaseOrder', [
                    'errors' => $e->errors(),
                    'order_number' => $this->order_number ?? 'N/A',
                ]);
                throw $e; // Re-lanzar para que Livewire muestre los errores
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Error en updatePurchaseOrder: '.$e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'order_number' => $this->order_number ?? 'N/A',
                    'id' => $id ?? 'N/A',
                ]);

                // Mostrar error al usuario
                $this->dispatch('show-error', 'Error al actualizar la orden: '.$e->getMessage());
                session()->flash('error', 'Error al actualizar la orden: '.$e->getMessage());

                // RETORNAR UN VALOR PARA INDICAR ERROR
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error crítico en updatePurchaseOrder: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'order_number' => $this->order_number ?? 'N/A',
                'id' => $id ?? 'N/A',
            ]);

            // Mostrar error al usuario
            $this->dispatch('show-error', 'Error al actualizar la orden: '.$e->getMessage());
            session()->flash('error', 'Error al actualizar la orden: '.$e->getMessage());

            // RETORNAR UN VALOR PARA INDICAR ERROR
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'modal-purchase-order-created');

        // Si tenemos un ID, redirigir a la página de detalle
        if ($this->id) {
            return redirect()->route('purchase-orders.detail', $this->id);
        } else {
            // Si no hay ID, redirigir al listado de POs sin parámetros de query
            // Esto evita que los filtros anteriores queden "pegados"
            // Usamos to() para construir una URL limpia sin parámetros de query
            return redirect()->to(route('purchase-orders.index'));
        }
    }

    public function render()
    {
        // Cargar los vendors y ship tos desde la base de datos
        $companyId = auth()->user()->company_id ?? 1;

        // Obtener los vendors y formatearlos para el selector
        $vendors = Vendor::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        // Si estamos editando, incluir el vendor actual de la PO aunque sea de otra empresa
        // (evita que el select muestre otro vendor cuando el actual no está en la lista)
        // vendor_id almacena vendo_code, no vendors.id
        if ($this->purchaseOrder && $this->vendor_id) {
            $currentVendor = Vendor::where('vendo_code', $this->vendor_id)->first();
            if ($currentVendor && ! $vendors->contains('vendo_code', $this->vendor_id)) {
                $vendors = $vendors->push($currentVendor);
            }
        }

        // Opciones: vendo_code => name (vendor_id en PO = vendo_code)
        $this->vendorArray = $vendors->filter(fn ($v) => $v->vendo_code !== null)->pluck('name', 'vendo_code')->toArray();

        // Obtener los ship tos y formatearlos para el selector
        $shipTos = ShipTo::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();
        $this->shipToArray = $shipTos->pluck('name', 'id')->toArray();

        // Las opciones de maestros se cargan solo cuando cambia trading_company (en updatedTradingCompany)

        return view('livewire.forms.create-pucharse-order');
    }

    /**
     * Ping liviano para evitar expiración de sesión durante ediciones largas de PO.
     */
    public function keepSessionAlive(): void
    {
        // No-op intencional. La petición Livewire renueva sesión y token CSRF.
    }

    public function updatedCostOfrEstimated()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    public function updatedCostOfrReal()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    public function updatedGroundTransportCost1()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    public function updatedGroundTransportCost2()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    public function updatedEstimatedPalletCost()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    public function updatedPalletQuantityReal()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    public function updatedOtherExpenses()
    {
        $this->calculateTotals();
        $this->calculateSavings();
    }

    public function calculateSavings()
    {
        // Ahorros OFR FCL = Costo OFR Real - Costo OFR Estimado
        $this->savings_ofr_fcl = floatval($this->cost_ofr_estimated) - floatval($this->cost_ofr_real);

        // Ahorro en Recogida → Ahorro pickup = Costo Transporte Terrestre 2 - Costo Transporte Terrestre 1
        $this->saving_pickup = floatval($this->ground_transport_cost_1) - floatval($this->ground_transport_cost_2);

        // Costo OFR Estimado = Costo Estimado de Pallets * Cantidad Real de Pallets
        if (! empty($this->pallet_quantity_real) && ! empty($this->estimated_pallet_cost)) {
            $this->cost_ofr_estimated = floatval($this->estimated_pallet_cost) * floatval($this->pallet_quantity_real);
        }
    }

    public function updatedFreightAmount()
    {
        $this->calculateTotals();
    }

    public function updatedCostNationalization()
    {
        $this->calculateTotals();
    }

    public function updatedOtherCosts()
    {
        $this->calculateTotals();
    }

    // Listeners para diferencias derivadas. El cumplimiento solo cambia con ATA.
    public function updatedDateEta()
    {
        $this->computeDateDiffs();
    }

    public function updatedDateEtaInitial()
    {
        $this->computeDateDiffs();
    }

    public function updatedDateEtd()
    {
        $this->computeDateDiffs();
    }

    public function updatedDateEtdInitial()
    {
        $this->computeDateDiffs();
    }

    public function updatedDateVariableDate()
    {
        $this->computeDateDiffs();
    }

    public function updatedDateTheoricalLoad()
    {
        $this->computeDateDiffs();
    }

    public function updatedDateAta()
    {
        $this->calculateDeliveryCompliance();
    }

    public function updatedTrackingNotApplicable($value): void
    {
        if (! $this->id) {
            return;
        }

        if (! (bool) $value) {
            return;
        }

        $this->restoreTrackingNotApplicableLockedFields((int) $this->id);
    }

    protected function computeDateDiffs(): void
    {
        try {
            // Usamos Carbon para diferencias con signo
            $etdInitial = $this->date_etd_initial ? \Carbon\Carbon::parse($this->date_etd_initial) : null;
            $etdUpdated = $this->date_etd ? \Carbon\Carbon::parse($this->date_etd) : null;

            $etaBase = $this->date_eta ? \Carbon\Carbon::parse($this->date_eta) : null;
            $etaUpdated = $this->date_eta_initial ? \Carbon\Carbon::parse($this->date_eta_initial) : null;

            $this->etd_dates_difference = ($etdInitial && $etdUpdated)
                ? $etdInitial->diffInDays($etdUpdated, true) //
                : null;

            $this->eta_dates_difference = ($etaBase && $etaUpdated)
                ? $etaBase->diffInDays($etaUpdated, true)
                : null;

            $this->dif_load_date = \App\Models\PurchaseOrder::calculateLoadDateDifference(
                $this->date_theorical_load,
                $this->date_variable_date
            );
        } catch (\Exception $e) {
            \Log::warning('Error en computeDateDiffs: '.$e->getMessage(), [
                'date_etd_initial' => $this->date_etd_initial,
                'date_etd' => $this->date_etd,
                'date_eta' => $this->date_eta,
                'date_eta_initial' => $this->date_eta_initial,
                'date_eta_updated' => $this->date_eta_updated,
                'date_theorical_load' => $this->date_theorical_load,
                'date_variable_date' => $this->date_variable_date,
            ]);
            // No lanzar excepción para no interrumpir el flujo
            $this->etd_dates_difference = null;
            $this->eta_dates_difference = null;
            $this->dif_load_date = null;
        }
    }

    protected function calculateDeliveryCompliance(): void
    {
        $compliance = \App\Models\PurchaseOrder::calculateDeliveryCompliance(
            $this->date_eta_initial,
            $this->date_ata
        );

        $this->arrival_status = $compliance['arrival_status'];
        $this->delay_days = $compliance['delay_days'];
    }

    public function calculateLoadDateDifference()
    {
        $this->dif_load_date = \App\Models\PurchaseOrder::calculateLoadDateDifference(
            $this->date_theorical_load,
            $this->date_variable_date
        );

        return $this->dif_load_date ?? '-';
    }

    /**
     * Normalize value for comparison to avoid false positives.
     * Handles different types and formats of values.
     */
    protected function normalizeValueForComparison($value)
    {
        // Handle null
        if ($value === null) {
            return null;
        }

        // Handle empty string and false as null
        if ($value === '' || $value === false) {
            return null;
        }

        // Handle Carbon dates
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }

        // Handle DateTime objects
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        // Handle strings - trim and normalize
        if (is_string($value)) {
            $trimmed = trim($value);
            // Empty string becomes null
            if ($trimmed === '') {
                return null;
            }
            // Check numeric FIRST to avoid Carbon::parse("0.00") returning "1970-01-01"
            if (is_numeric($trimmed)) {
                $floatValue = (float) $trimmed;

                return round($floatValue, 2);
            }
            // Try to parse as date only if it looks like a date (starts with YYYY-MM-DD pattern)
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $trimmed)) {
                try {
                    $parsedDate = \Carbon\Carbon::parse($trimmed);

                    return $parsedDate->format('Y-m-d');
                } catch (\Exception $e) {
                    // Not a date, continue
                }
            }

            return $trimmed;
        }

        // Handle numeric values - normalize to float with 2 decimal places
        if (is_numeric($value)) {
            $floatValue = (float) $value;

            return round($floatValue, 2);
        }

        // Handle booleans
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        // Handle arrays - convert to JSON string for comparison
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // For objects, convert to string
        if (is_object($value)) {
            return (string) $value;
        }

        return $value;
    }

    protected function dateValueChanged($oldValue, $newValue): bool
    {
        $normalize = function ($value) {
            if (blank($value)) {
                return null;
            }

            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return $value;
            }
        };

        return $normalize($oldValue) !== $normalize($newValue);
    }

    protected function shouldRequestTrackingNotApplicable(\App\Models\PurchaseOrder $purchaseOrder): bool
    {
        if ((bool) $purchaseOrder->tracking_not_applicable) {
            return false;
        }

        if (! (bool) $this->tracking_not_applicable) {
            return false;
        }

        return ! $purchaseOrder->hasAuthorizationPending('tracking_not_applicable');
    }

    protected function shouldLockTrackingNotApplicableFields(): bool
    {
        return (bool) $this->tracking_not_applicable
            || (bool) $this->trackingNotApplicableApproved
            || (bool) $this->trackingNotApplicablePending;
    }

    protected function shouldLockTrackingDateFields(\App\Models\PurchaseOrder $purchaseOrder): bool
    {
        return ! (bool) $purchaseOrder->tracking_not_applicable;
    }

    protected function shouldLockEtdInitial(\App\Models\PurchaseOrder $purchaseOrder): bool
    {
        if ((bool) $purchaseOrder->tracking_not_applicable) {
            return false;
        }

        return filled($purchaseOrder->date_etd_initial);
    }

    protected function shouldLockEtaInitial(\App\Models\PurchaseOrder $purchaseOrder): bool
    {
        if ((bool) $purchaseOrder->tracking_not_applicable) {
            return false;
        }

        return filled($purchaseOrder->date_eta_initial);
    }

    protected function restoreEditLockedApiFields(int $purchaseOrderId): void
    {
        $purchaseOrder = \App\Models\PurchaseOrder::with('vendor')->find($purchaseOrderId);

        if (! $purchaseOrder) {
            return;
        }

        $vendorCode = $purchaseOrder->vendor_number ?? $purchaseOrder->vendor?->vendo_code ?? '';

        $this->order_number = $purchaseOrder->order_number;
        $this->vendor_id = $vendorCode;
        $this->vendor_number = $vendorCode;
        $this->retail_group = $purchaseOrder->retail_group;
        $this->route_label = $purchaseOrder->route_label;
        $this->net_total = $purchaseOrder->net_total;
        $this->total_amount = $purchaseOrder->total_amount;
        $this->currency = $purchaseOrder->currency;
        $this->emision_date_po = optional($purchaseOrder->emision_date_po)?->format('Y-m-d');
        $this->category = $purchaseOrder->category;
        $this->incoterms = $purchaseOrder->incoterms;
        $this->date_theorical_load = optional($purchaseOrder->date_theorical_load)?->format('Y-m-d');
        $this->factory_proforma_number = $purchaseOrder->factory_proforma_number;
        $this->reason = $purchaseOrder->reason;
        $this->customer_type = $purchaseOrder->customer_type;
    }

    protected function withoutEditLockedApiFields(array $poData): array
    {
        foreach (self::EDIT_LOCKED_API_FIELDS as $field) {
            unset($poData[$field]);
        }

        return $poData;
    }

    protected function restoreEditLockedTrackingDateFields(int $purchaseOrderId): void
    {
        $purchaseOrder = \App\Models\PurchaseOrder::find($purchaseOrderId);
        if (! $purchaseOrder) {
            $this->trackingDatesLocked = false;
            return;
        }

        $this->etdInitialLocked = $this->shouldLockEtdInitial($purchaseOrder);
        $this->trackingDatesLocked = $this->shouldLockTrackingDateFields($purchaseOrder);
        $this->date_etd_initial = optional($purchaseOrder->date_etd_initial)?->format('Y-m-d');
        $this->date_etd = optional($purchaseOrder->date_etd)?->format('Y-m-d');
        $this->date_atd = optional($purchaseOrder->date_atd)?->format('Y-m-d');
        $this->date_eta_initial = optional($purchaseOrder->date_eta_initial)?->format('Y-m-d');
        $this->date_eta = optional($purchaseOrder->date_eta)?->format('Y-m-d');
        $this->date_eta_updated = optional($purchaseOrder->date_eta)?->format('Y-m-d');
        $this->date_ata = optional($purchaseOrder->date_ata)?->format('Y-m-d');
    }

    protected function withoutEditLockedTrackingDateFields(array $poData): array
    {
        foreach (self::EDIT_LOCKED_TRACKING_DATE_FIELDS as $field) {
            unset($poData[$field]);
        }

        return $poData;
    }

    protected function restoreTrackingNotApplicableLockedFields(int $purchaseOrderId): void
    {
        $purchaseOrder = \App\Models\PurchaseOrder::find($purchaseOrderId);

        if (! $purchaseOrder) {
            return;
        }

        $this->container_type = $purchaseOrder->container_type;
        $this->container_number = $purchaseOrder->container_number;
        $this->shipping_line = $purchaseOrder->shipping_line;
    }

    protected function withoutTrackingNotApplicableLockedFields(array $poData): array
    {
        foreach (self::TRACKING_NOT_APPLICABLE_LOCKED_FIELDS as $field) {
            unset($poData[$field]);
        }

        return $poData;
    }

    protected function hasPorthTrackingActive(\App\Models\PurchaseOrder $purchaseOrder): bool
    {
        if ((bool) $purchaseOrder->tracking_not_applicable) {
            return false;
        }

        return filled($purchaseOrder->porth_id) || filled($purchaseOrder->last_porth_sync_at);
    }

    /**
     * Clamp numeric fields to avoid PostgreSQL overflow (decimal precision).
     * decimal(10,2) max = 99.999.999,99 | decimal(12,2) max = 9.999.999.999,99 | decimal(8,2) max = 99.999,99
     */
    protected function clampDecimalFieldsForDb(array $poData): array
    {
        $max10_2 = 99999999.99;
        $max12_2 = 9999999999.99;
        $max8_2 = 999999.99;

        $decimal10_2 = [
            'cost_ofr_estimated', 'cost_ofr_real', 'estimated_pallet_cost', 'real_cost_estimated_po',
            'real_cost_real_po', 'other_costs', 'other_expenses', 'variable_calculare_weight',
            'savings_ofr_fcl', 'saving_pickup', 'saving_executed', 'saving_not_executed',
            'insurance_cost', 'ground_transport_cost_1', 'ground_transport_cost_2', 'cost_nationalization',
            'Invoice_amount', 'freight_amount', 'cbm', 'length_cm', 'width_cm', 'height_cm',
        ];
        $decimal12_2 = ['net_total', 'additional_cost', 'total', 'total_amount'];
        $decimal8_2 = ['length', 'width', 'height', 'weight_kg', 'weight_lb'];
        $decimal10_3 = ['volume'];

        foreach ($decimal10_2 as $field) {
            if (isset($poData[$field]) && is_numeric($poData[$field])) {
                $v = (float) $poData[$field];
                if (abs($v) > $max10_2) {
                    $poData[$field] = $v < 0 ? -$max10_2 : $max10_2;
                }
            }
        }
        foreach ($decimal12_2 as $field) {
            if (isset($poData[$field]) && is_numeric($poData[$field])) {
                $v = (float) $poData[$field];
                if (abs($v) > $max12_2) {
                    $poData[$field] = $v < 0 ? -$max12_2 : $max12_2;
                }
            }
        }
        foreach ($decimal8_2 as $field) {
            if (isset($poData[$field]) && is_numeric($poData[$field])) {
                $v = (float) $poData[$field];
                if (abs($v) > $max8_2) {
                    $poData[$field] = $v < 0 ? -$max8_2 : $max8_2;
                }
            }
        }
        $max10_3 = 9999999.999;
        foreach ($decimal10_3 as $field) {
            if (isset($poData[$field]) && is_numeric($poData[$field])) {
                $v = (float) $poData[$field];
                if (abs($v) > $max10_3) {
                    $poData[$field] = $v < 0 ? -$max10_3 : $max10_3;
                }
            }
        }

        return $poData;
    }
}
