<?php
/**
 * AURL.php | Dec 21, 2013
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @package BLW\Mail
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type;

use ReflectionMethod;

use BLW\Model\InvalidArgumentException;


if (! defined('BLW')) {

    if (strstr($_SERVER['PHP_SELF'], basename(__FILE__))) {
        header("$_SERVER[SERVER_PROTOCOL] 404 Not Found");
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;

        echo "<html>\r\n<head><title>404 Not Found</title></head><body bgcolor=\"white\">\r\n<center><h1>404 Not Found</h1></center>\r\n<hr>\r\n<center>nginx/1.5.9</center>\r\n</body>\r\n</html>\r\n";
        exit();
    }

    return false;
}

/**
 * Abstract class for all URL's and URI's
 *
 * <h3>RFC 3986</h3>
 *
 * <pre>
 * foo://example.com:8042/over/there?name=ferret#nose
 * \_/ \______________/\_________/ \_________/ \__/
 * | | | | |
 * scheme authority path query fragment
 * | _____________________|__
 * / \ / \
 * urn:example:animal:ferret:nose
 * </pre>
 *
 * <pre>
 * URI-reference := URI / relative-ref
 *
 * URI           := scheme ":" hier-part ["?" query] ["#" fragment]
 *
 * relative-ref  := relative-part ["?" query] ["#" fragment]
 *
 * absolute-URI  := scheme ":" hier-part ["?" query]
 * </pre>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +--------------------+
 * | URI                                               |<------| SERIALIZABLE       |
 * +---------------------------------------------------+       | ================== |
 * | AS_STRING                                         |       | Serializable       |
 * | AS_ARRAY                                          |       +--------------------+
 * +---------------------------------------------------+<------| ITERABLE           |
 * | [scheme]:       string                            |       +--------------------+
 * | [userinfo]:     string                            |<------| IFACTORY           |
 * | [host]:         string                            |       +--------------------+
 * | [port]:         string                            |<------| ArrayAccess        |
 * | [path]:         string                            |       +--------------------+
 * | [query]:        string                            |<------| Countable          |
 * | [fragment]:     string                            |       +--------------------+
 * | [IPv4Address]:  string                            |<------| IteratorAggregate  |
 * | [IPv4Address]:  string                            |       +--------------------+
 * +---------------------------------------------------+
 * | _Storage: array                                   |
 * +---------------------------------------------------+
 * | createString(): string                            |
 * |                                                   |
 * | $Parts:  array                                    |
 * +---------------------------------------------------+
 * | __construct():                                    |
 * |                                                   |
 * | $URI:  string                                     |
 * +---------------------------------------------------+
 * | getRegex(): string                                |
 * +---------------------------------------------------+
 * | removeDotSegments(): string                       |
 * |                                                   |
 * | $path:  string                                    |
 * +---------------------------------------------------+
 * | parseTLD(): array                                 |
 * |                                                   |
 * | $Domain:  string                                  |
 * +---------------------------------------------------+
 * | parse(): array                                    |
 * |                                                   |
 * | $URI:      string                                 |
 * | $baseURI:  array                                  |
 * +---------------------------------------------------+
 * | resolve(): array|string                           |
 * |                                                   |
 * | $path:   string                                   |
 * | $flags:  IURL::RESOLVE_FLAGS                      |
 * +---------------------------------------------------+
 * | isValid(): bool                                   |
 * +---------------------------------------------------+
 * | __tostring(): createString(_Storage)              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://www.php.net/manual/en/function.parse-url.php parse_url()
 * @link http://tools.ietf.org/html/rfc3986 RFC3986
 */
abstract class AURI extends \BLW\Type\ASerializable /*, \BLW\Type\AIterable */ implements\BLW\Type\IURI
{

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Pointer to current parent of object.
     *
     * @var \BLW\Type\IObject $Parent
     */
    protected $_Parent = null;

#############################################################################################
# ArrayAccess Trait
#############################################################################################

    /**
     * Array used for implememnting ArrayAccess methods.
     *
     * @var array $_Storage
     */
    protected $_Storage = array();

#############################################################################################
# URI Trait
#############################################################################################

    /**
     * Parts of URI.
     *
     * @var array $_Default
     */
    protected static $_Default = array(
        'scheme'      => '',
        'userinfo'    => '',
        'host'        => '',
        'port'        => '',
        'path'        => '',
        'query'       => array(),
        'fragment'    => '',
        'IPv4Address' => '',
        'IPv6Address' => '',
        'TLD'         => ''
    );

    /**
     *
     * @todo Add non-ASCII domains.
     * @var array $_TLDs Valid Top Level Domains.
     * @link http://www.iana.org/domains/root/db IANA.org
     */
    protected static $_TLDs = array(
        'ac' => 'Network Information Center (AC Domain Registry) c/o Cable and Wireless (Ascension Island)',
        'academy' => 'Half Oaks, LLC',
        'actor' => 'United TLD Holdco Ltd.',
        'ad' => 'Andorra Telecom',
        'ae' => 'Telecommunication Regulatory Authority (TRA)',
        'aero' => 'Societe Internationale de Telecommunications Aeronautique (SITA INC USA)',
        'af' => 'Ministry of Communications and IT',
        'ag' => 'UHSA School of Medicine',
        'agency' => 'Steel Falls, LLC',
        'ai' => 'Government of Anguilla',
        'al' => 'Electronic and Postal Communications Authority - AKEP',
        'am' => 'Internet Society',
        'an' => 'University of Curacao',
        'ao' => 'Faculdade de Engenharia da Universidade Agostinho Neto',
        'aq' => 'Mott and Associates',
        'ar' => 'Presidencia de la Nación – Secretaría Legal y Técnica',
        'arpa' => 'Internet Assigned Numbers Authority',
        'as' => 'AS Domain Registry',
        'asia' => 'DotAsia Organisation Ltd.',
        'at' => 'nic.at GmbH',
        'au' => '.au Domain Administration (auDA)',
        'aw' => 'SETAR',
        'ax' => 'Ålands landskapsregering',
        'az' => 'IntraNS',
        'ba' => 'Universtiy Telinformatic Centre (UTIC)',
        'bargains' => 'Half Hallow, LLC',
        'bb' => 'Government of Barbados Ministry of Economic Affairs and Development Telecommunications Unit',
        'bd' => 'Ministry of Post & Telecommunications Bangladesh Secretariat',
        'be' => 'DNS BE vzw/asbl',
        'berlin' => 'dotBERLIN GmbH & Co. KG',
        'bf' => 'ARCE-AutoritÈ de RÈgulation des Communications Electroniques',
        'bg' => 'Register.BG',
        'bh' => 'Telecommunications Regulatory Authority (TRA)',
        'bi' => 'Centre National de l\'Informatique',
        'bike' => 'Grand Hollow, LLC',
        'biz' => 'NeuStar, Inc.',
        'bj' => 'Benin Telecoms S.A.',
        'bl' => 'Not assigned',
        'blue' => 'Afilias Limited',
        'bm' => 'Registry General Ministry of Labour and Immigration',
        'bn' => 'Telekom Brunei Berhad',
        'bo' => 'Agencia para el Desarrollo de la Información de la Sociedad en Bolivia',
        'boutique' => 'Over Galley, LLC',
        'bq' => 'Not assigned',
        'br' => 'Comite Gestor da Internet no Brasil',
        'bs' => 'The College of the Bahamas',
        'bt' => 'Ministry of Information and Communications',
        'build' => 'Plan Bee LLC',
        'builders' => 'Atomic Madison, LLC',
        'buzz' => 'DOTSTRATEGY CO.',
        'bv' => 'UNINETT Norid A/S',
        'bw' => 'Botswana Communications Regulatory Authority (BOCRA)',
        'by' => 'Reliable Software Inc.',
        'bz' => 'University of Belize',
        'ca' => 'Canadian Internet Registration Authority (CIRA) Autorite Canadienne pour les Enregistrements Internet (ACEI)',
        'cab' => 'Half Sunset, LLC',
        'camera' => 'Atomic Maple, LLC',
        'camp' => 'Delta Dynamite, LLC',
        'cards' => 'Foggy Hollow, LLC',
        'careers' => 'Wild Corner, LLC',
        'cat' => 'Fundacio puntCAT',
        'catering' => 'New Falls. LLC',
        'cc' => 'eNIC Cocos (Keeling) Islands Pty. Ltd. d/b/a Island Internet Services',
        'cd' => 'Office Congolais des Postes et Télécommunications - OCPT',
        'center' => 'Tin Mill, LLC',
        'ceo' => 'CEOTLD Pty Ltd',
        'cf' => 'Societe Centrafricaine de Telecommunications (SOCATEL)',
        'cg' => 'ONPT Congo and Interpoint Switzerland',
        'ch' => 'SWITCH The Swiss Education & Research Network',
        'cheap' => 'Sand Cover, LLC',
        'christmas' => 'Uniregistry, Corp.',
        'ci' => 'INP-HB Institut National Polytechnique Felix Houphouet Boigny',
        'ck' => 'Telecom Cook Islands Ltd.',
        'cl' => 'NIC Chile (University of Chile)',
        'cleaning' => 'Fox Shadow, LLC',
        'clothing' => 'Steel Lake, LLC',
        'club' => '.CLUB DOMAINS, LLC',
        'cm' => 'Cameroon Telecommunications (CAMTEL)',
        'cn' => 'Computer Network Information Center, Chinese Academy of Sciences',
        'co' => '.CO Internet S.A.S.',
        'codes' => 'Puff Willow, LLC',
        'coffee' => 'Trixy Cover, LLC',
        'com' => 'VeriSign Global Registry Services',
        'community' => 'Fox Orchard, LLC',
        'company' => 'Silver Avenue, LLC',
        'computer' => 'Pine Mill, LLC',
        'construction' => 'Fox Dynamite, LLC',
        'contractors' => 'Magic Woods, LLC',
        'cool' => 'Koko Lake, LLC',
        'coop' => 'DotCooperation LLC',
        'cr' => 'National Academy of Sciences Academia Nacional de Ciencias',
        'cruises' => 'Spring Way, LLC',
        'cu' => 'CENIAInternet Industria y San Jose Capitolio Nacional',
        'cv' => 'Agência Nacional das Comunicações (ANAC)',
        'cw' => 'University of Curacao',
        'cx' => 'Christmas Island Internet Administration Limited',
        'cy' => 'University of Cyprus',
        'cz' => 'CZ.NIC, z.s.p.o',
        'dance' => 'United TLD Holdco Ltd.',
        'dating' => 'Pine Fest, LLC',
        'de' => 'DENIC eG',
        'democrat' => 'United TLD Holdco Ltd.',
        'diamonds' => 'John Edge, LLC',
        'directory' => 'Extra Madison, LLC',
        'dj' => 'Djibouti Telecom S.A',
        'dk' => 'Dansk Internet Forum',
        'dm' => 'DotDM Corporation',
        'do' => 'Pontificia Universidad Catolica Madre y Maestra Recinto Santo Tomas de Aquino',
        'domains' => 'Sugar Cross, LLC',
        'dz' => 'CERIST',
        'ec' => 'NIC.EC (NICEC) S.A.',
        'edu' => 'EDUCAUSE',
        'education' => 'Brice Way, LLC',
        'ee' => 'Eesti Interneti Sihtasutus (EIS)',
        'eg' => 'Egyptian Universities Network (EUN) Supreme Council of Universities',
        'eh' => 'Not assigned',
        'email' => 'Spring Madison, LLC',
        'enterprises' => 'Snow Oaks, LLC',
        'equipment' => 'Corn Station, LLC',
        'er' => 'Eritrea Telecommunication Services Corporation (EriTel)',
        'es' => 'Red.es',
        'estate' => 'Trixy Park, LLC',
        'et' => 'Ethio telecom',
        'eu' => 'EURid vzw/asbl',
        'events' => 'Pioneer Maple, LLC',
        'expert' => 'Magic Pass, LLC',
        'exposed' => 'Victor Beach, LLC',
        'farm' => 'Just Maple, LLC',
        'fi' => 'Finnish Communications Regulatory Authority',
        'fish' => 'Fox Woods, LLC',
        'fj' => 'The University of the South Pacific IT Services',
        'fk' => 'Falkland Islands Government',
        'flights' => 'Fox Station, LLC',
        'florist' => 'Half Cypress, LLC',
        'fm' => 'FSM Telecommunications Corporation',
        'fo' => 'FO Council',
        'foundation' => 'John Dale, LLC',
        'fr' => 'AFNIC (NIC France) - Immeuble International',
        'futbol' => 'United TLD Holdco, Ltd.',
        'ga' => 'Agence Nationale des Infrastructures Numériques et des Fréquences (ANINF)',
        'gallery' => 'Sugar House, LLC',
        'gb' => 'Reserved Domain - IANA',
        'gd' => 'The National Telecommunications Regulatory Commission (NTRC)',
        'ge' => 'Caucasus Online',
        'gf' => 'Net Plus',
        'gg' => 'Island Networks Ltd.',
        'gh' => 'Network Computer Systems Limited',
        'gi' => 'Sapphire Networks',
        'gift' => 'Uniregistry, Corp.',
        'gl' => 'TELE Greenland A/S',
        'glass' => 'Black Cover, LLC',
        'gm' => 'GM-NIC',
        'gn' => 'Centre National des Sciences Halieutiques de Boussoura',
        'gov' => 'General Services Administration Attn: QTDC, 2E08 (.gov Domain Registration)',
        'gp' => 'Networking Technologies Group',
        'gq' => 'GETESA',
        'gr' => 'ICS-FORTH GR',
        'graphics' => 'Over Madison, LLC',
        'gs' => 'Government of South Georgia and South Sandwich Islands (GSGSSI)',
        'gt' => 'Universidad del Valle de Guatemala',
        'gu' => 'University of Guam Computer Center',
        'guitars' => 'Uniregistry, Corp.',
        'guru' => 'Pioneer Cypress, LLC',
        'gw' => 'Fundação IT & MEDIA Universidade de Bissao',
        'gy' => 'University of Guyana',
        'hk' => 'Hong Kong Internet Registration Corporation Ltd.',
        'hm' => 'HM Domain Registry',
        'hn' => 'Red de Desarrollo Sostenible Honduras',
        'holdings' => 'John Madison, LLC',
        'holiday' => 'Goose Woods, LLC',
        'house' => 'Sugar Park, LLC',
        'hr' => 'CARNet - Croatian Academic and Research Network',
        'ht' => 'Consortium FDS/RDDH',
        'hu' => 'Council of Hungarian Internet Providers (CHIP)',
        'id' => 'Perkumpulan Pengelola Nama Domain Internet Indonesia (PANDI)',
        'ie' => 'University College Dublin Computing Services Computer Centre',
        'il' => 'Internet Society of Israel',
        'im' => 'Isle of Man Government',
        'immobilien' => 'United TLD Holdco Ltd.',
        'in' => 'National Internet Exchange of India',
        'industries' => 'Outer House, LLC',
        'info' => 'Afilias Limited',
        'institute' => 'Outer Maple, LLC',
        'int' => 'Internet Assigned Numbers Authority',
        'international' => 'Wild Way, LLC',
        'io' => 'IO Top Level Domain Registry Cable and Wireless',
        'iq' => 'Communications and Media Commission (CMC)',
        'ir' => 'Institute for Research in Fundamental Sciences',
        'is' => 'ISNIC - Internet Iceland ltd.',
        'it' => 'IIT - CNR',
        'je' => 'Island Networks (Jersey) Ltd.',
        'jm' => 'University of West Indies',
        'jo' => 'National Information Technology Center (NITC)',
        'jobs' => 'Employ Media LLC',
        'jp' => 'Japan Registry Services Co., Ltd.',
        'kaufen' => 'United TLD Holdco Ltd.',
        'ke' => 'Kenya Network Information Center (KeNIC)',
        'kg' => 'AsiaInfo Telecommunication Enterprise',
        'kh' => 'Ministry of Post and Telecommunications',
        'ki' => 'Ministry of Communications, Transport, and Tourism Development',
        'kim' => 'Afilias Limited',
        'kitchen' => 'Just Goodbye, LLC',
        'kiwi' => 'DOT KIWI LIMITED',
        'km' => 'Comores Telecom',
        'kn' => 'Ministry of Finance, Sustainable Development Information & Technology',
        'kp' => 'Star Joint Venture Company',
        'kr' => 'Korea Internet & Security Agency (KISA)',
        'kw' => 'Ministry of Communications',
        'ky' => 'The Information and Communications Technology Authority',
        'kz' => 'Association of IT Companies of Kazakhstan',
        'la' => 'Lao National Internet Committee (LANIC), Ministry of Posts and Telecommunications',
        'land' => 'Pine Moon, LLC',
        'lb' => 'American University of Beirut Computing and Networking Services',
        'lc' => 'University of Puerto Rico',
        'li' => 'Universitaet Liechtenstein',
        'lighting' => 'John McCook, LLC',
        'limo' => 'Hidden Frostbite, LLC',
        'link' => 'Uniregistry, Corp.',
        'lk' => 'Council for Information Technology LK Domain Registrar',
        'lr' => 'Data Technology Solutions, Inc.',
        'ls' => 'National University of Lesotho',
        'lt' => 'Kaunas University of Technology',
        'lu' => 'RESTENA',
        'luxury' => 'Luxury Partners LLC',
        'lv' => 'University of Latvia Institute of Mathematics and Computer Science Department of Network Solutions (DNS)',
        'ly' => 'General Post and Telecommunication Company',
        'ma' => 'Agence Nationale de Réglementation des Télécommunications (ANRT)',
        'management' => 'John Goodbye, LLC',
        'mango' => 'PUNTO FA S.L.',
        'marketing' => 'Fern Pass, LLC',
        'mc' => 'Gouvernement de Monaco Direction des Communications Electroniques',
        'md' => 'MoldData S.E.',
        'me' => 'Government of Montenegro',
        'menu' => 'Wedding TLD2, LLC',
        'mf' => 'Not assigned',
        'mg' => 'NIC-MG (Network Information Center Madagascar)',
        'mh' => 'Office of the Cabinet',
        'mil' => 'DoD Network Information Center',
        'mk' => 'Ministry of Foreign Affairs',
        'ml' => 'Agence des Technologies de l’Information et de la Communication',
        'mm' => 'Ministry of Communications, Posts & Telegraphs',
        'mn' => 'Datacom Co., Ltd.',
        'mo' => 'Bureau of Telecommunications Regulation (DSRT)',
        'mobi' => 'Afilias Technologies Limited dba dotMobi',
        'moda' => 'United TLD Holdco Ltd.',
        'monash' => 'Monash University',
        'mp' => 'Saipan Datacom, Inc.',
        'mq' => 'MEDIASERV',
        'mr' => 'University of Nouakchott',
        'ms' => 'MNI Networks Ltd.',
        'mt' => 'NIC (Malta)',
        'mu' => 'Internet Direct Ltd',
        'museum' => 'Museum Domain Management Association',
        'mv' => 'Dhiraagu Pvt. Ltd. (DHIVEHINET)',
        'mw' => 'Malawi Sustainable Development Network Programme (Malawi SDNP)',
        'mx' => 'NIC-Mexico ITESM - Campus Monterrey',
        'my' => 'MYNIC Berhad',
        'mz' => 'Centro de Informatica de Universidade Eduardo Mondlane',
        'na' => 'Namibian Network Information Center',
        'nagoya' => 'GMO Registry, Inc.',
        'name' => 'VeriSign Information Services, Inc.',
        'nc' => 'Office des Postes et Telecommunications',
        'ne' => 'SONITEL',
        'net' => 'VeriSign Global Registry Services',
        'neustar' => 'NeuStar, Inc.',
        'nf' => 'Norfolk Island Data Services',
        'ng' => 'Nigeria Internet Registration Association',
        'ni' => 'Universidad Nacional del Ingernieria Centro de Computo',
        'ninja' => 'United TLD Holdco Ltd.',
        'nl' => 'SIDN (Stichting Internet Domeinregistratie Nederland)',
        'no' => 'UNINETT Norid A/S',
        'np' => 'Mercantile Communications Pvt. Ltd.',
        'nr' => 'CENPAC NET',
        'nu' => 'The IUSN Foundation',
        'nz' => 'InternetNZ',
        'om' => 'Telecommunications Regulatory Authority (TRA)',
        'onl' => 'I-REGISTRY Ltd., Niederlassung Deutschland',
        'org' => 'Public Interest Registry (PIR)',
        'pa' => 'Universidad Tecnologica de Panama',
        'partners' => 'Magic Glen, LLC',
        'parts' => 'Sea Goodbye, LLC',
        'pe' => 'Red Cientifica Peruana',
        'pf' => 'Gouvernement de la Polynésie française',
        'pg' => 'PNG DNS Administration Vice Chancellors Office The Papua New Guinea University of Technology',
        'ph' => 'PH Domain Foundation',
        'photo' => 'Uniregistry, Corp.',
        'photography' => 'Sugar Glen, LLC',
        'photos' => 'Sea Corner, LLC',
        'pics' => 'Uniregistry, Corp.',
        'pink' => 'Afilias Limited',
        'pk' => 'PKNIC',
        'pl' => 'Research and Academic Computer Network',
        'plumbing' => 'Spring Tigers, LLC',
        'pm' => 'AFNIC (NIC France) - Immeuble International',
        'pn' => 'Pitcairn Island Administration',
        'post' => 'Universal Postal Union',
        'pr' => 'Gauss Research Laboratory Inc.',
        'pro' => 'Registry Services Corporation dba RegistryPro',
        'productions' => 'Magic Birch, LLC',
        'properties' => 'Big Pass, LLC',
        'ps' => 'Ministry Of Telecommunications & Information Technology, Government Computer Center.',
        'pt' => 'Associação DNS.PT',
        'pub' => 'United TLD Holdco Ltd.',
        'pw' => 'Micronesia Investment and Development Corporation',
        'py' => 'NIC-PY',
        'qa' => 'The Supreme Council of Information and Communication Technology (ictQATAR)',
        'qpon' => 'dotCOOL, Inc.',
        're' => 'AFNIC (NIC France) - Immeuble International',
        'recipes' => 'Grand Island, LLC',
        'red' => 'Afilias Limited',
        'rentals' => 'Big Hollow,LLC',
        'repair' => 'Lone Sunset, LLC',
        'report' => 'Binky Glen, LLC',
        'reviews' => 'United TLD Holdco, Ltd.',
        'rich' => 'I-REGISTRY Ltd., Niederlassung Deutschland',
        'ro' => 'National Institute for R&D in Informatics',
        'rs' => 'Serbian National Register of Internet Domain Names (RNIDS)',
        'ru' => 'Coordination Center for TLD RU',
        'ruhr' => 'regiodot GmbH & Co. KG',
        'rw' => 'Rwanda Information Communication and Technology Association (RICTA)',
        'sa' => 'Communications and Information Technology Commission',
        'sb' => 'Solomon Telekom Company Limited',
        'sc' => 'VCS Pty Ltd',
        'sd' => 'Sudan Internet Society',
        'se' => 'The Internet Infrastructure Foundation',
        'sexy' => 'Uniregistry, Corp.',
        'sg' => 'Singapore Network Information Centre (SGNIC) Pte Ltd',
        'sh' => 'Government of St. Helena',
        'shiksha' => 'Afilias Limited',
        'shoes' => 'Binky Galley, LLC',
        'si' => 'Academic and Research Network of Slovenia (ARNES)',
        'singles' => 'Fern Madison, LLC',
        'sj' => 'UNINETT Norid A/S',
        'sk' => 'SK-NIC, a.s.',
        'sl' => 'Sierratel',
        'sm' => 'Telecom Italia San Marino S.p.A.',
        'sn' => 'Universite Cheikh Anta Diop NIC Senegal',
        'so' => 'Ministry of Post and Telecommunications',
        'social' => 'United TLD Holdco Ltd.',
        'solar' => 'Ruby Town, LLC',
        'solutions' => 'Silver Cover, LLC',
        'sr' => 'Telesur',
        'ss' => 'Not assigned',
        'st' => 'Tecnisys',
        'su' => 'Russian Institute for Development of Public Networks (ROSNIIROS)',
        'supplies' => 'Atomic Fields, LLC',
        'supply' => 'Half Falls, LLC',
        'support' => 'Grand Orchard, LLC',
        'sv' => 'SVNet',
        'sx' => 'SX Registry SA B.V.',
        'sy' => 'National Agency for Network Services (NANS)',
        'systems' => 'Dash Cypress, LLC',
        'sz' => 'University of Swaziland Department of Computer Science',
        'tattoo' => 'Uniregistry, Corp.',
        'tc' => 'Melrex TC',
        'td' => 'Société des télécommunications du Tchad (SOTEL TCHAD)',
        'technology' => 'Auburn Falls, LLC',
        'tel' => 'Telnic Ltd.',
        'tf' => 'AFNIC (NIC France) - Immeuble International',
        'tg' => 'Cafe Informatique et Telecommunications',
        'th' => 'Thai Network Information Center Foundation',
        'tienda' => 'Victor Manor, LLC',
        'tips' => 'Corn Willow, LLC',
        'tj' => 'Information Technology Center',
        'tk' => 'Telecommunication Tokelau Corporation (Teletok)',
        'tl' => 'Ministry of Infrastructure Information and Technology Division',
        'tm' => 'TM Domain Registry Ltd',
        'tn' => 'Agence Tunisienne d\'Internet',
        'to' => 'Government of the Kingdom of Tonga H.R.H. Crown Prince Tupouto\'a c/o Consulate of Tonga',
        'today' => 'Pearl Woods, LLC',
        'tokyo' => 'GMO Registry, Inc.',
        'tools' => 'Pioneer North, LLC',
        'tp' => '-',
        'tr' => 'Middle East Technical University Department of Computer Engineering',
        'training' => 'Wild Willow, LLC',
        'travel' => 'Tralliance Registry Management Company, LLC.',
        'tt' => 'University of the West Indies Faculty of Engineering',
        'tv' => 'Ministry of Finance and Tourism',
        'tw' => 'Taiwan Network Information Center (TWNIC)',
        'tz' => 'Tanzania Network Information Centre (tzNIC)',
        'ua' => 'Communication Systems Ltd',
        'ug' => 'Uganda Online Ltd.',
        'uk' => 'Nominet UK',
        'um' => 'Not assigned',
        'uno' => 'Dot Latin LLC',
        'us' => 'NeuStar, Inc.',
        'uy' => 'SeCIU - Universidad de la Republica',
        'uz' => 'Computerization and Information Technologies Developing Center UZINFOCOM',
        'va' => 'Holy See Secretariat of State Department of Telecommunications',
        'vacations' => 'Atomic Tigers, LLC',
        'vc' => 'Ministry of Telecommunications, Science, Technology and Industry',
        've' => 'Comisión Nacional de Telecomunicaciones (CONATEL)',
        'ventures' => 'Binky Lake, LLC',
        'vg' => 'Pinebrook Developments Ltd',
        'vi' => 'Virgin Islands Public Telcommunications System c/o COBEX Internet Services',
        'viajes' => 'Black Madison, LLC',
        'villas' => 'New Sky, LLC',
        'vision' => 'Koko Station, LLC',
        'vn' => 'Ministry of Information and Communications of Socialist Republic of Viet Nam',
        'voting' => 'Valuetainment Corp.',
        'voyage' => 'Ruby House, LLC',
        'vu' => 'Telecom Vanuatu Limited',
        'wang' => 'Zodiac Leo Limited',
        'watch' => 'Sand Shadow, LLC',
        'wed' => 'Atgron, Inc.',
        'wf' => 'AFNIC (NIC France) - Immeuble International',
        'wien' => 'punkt.wien GmbH',
        'wiki' => 'Top Level Design, LLC',
        'works' => 'Little Dynamite, LLC',
        'ws' => 'Government of Samoa Ministry of Foreign Affairs & Trade',
        'xxx' => 'ICM Registry LLC',
        'xyz' => 'XYZ.COM LLC',
        'ye' => 'TeleYemen',
        'yt' => 'AFNIC (NIC France) - Immeuble International',
        'za' => 'ZA Domain Name Authority',
        'zm' => 'ZAMNET Communication Systems Ltd.',
        'zone' => 'Outer Falls, LLC',
        'zw' => 'Postal and Telecommunications Regulatory Authority of Zimbabwe (POTRAZ)'
    );

#############################################################################################




#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Retrieves the current parent of the object.
     *
     * @return \BLW\Type\IObject Returns <code>null</code> if no parent is set.
     */
    final public function getParent()
    {
        return $this->_Parent;
    }

    /**
     * Sets parent of the current object if null.
     *
     * @internal This is a one shot function (Only works once).
     *
     * @param mised $Parent
     *            New parent of object. (IObject|IContainer|IObjectStorage)
     * @return int Returns a <code>DataMapper</code> status code.
     */
    final public function setParent($Parent)
    {
        // Make sur object is not a parent of itself
        if ($Parent === $this)
            return IDataMapper::INVALID;

        // Make sure parent is valid
        elseif (! $Parent instanceof IObject && ! $Parent instanceof IContainer && ! $Parent instanceof IObjectStorage && ! $Parent instanceof IWrapper)
            return IDataMapper::INVALID;

        // Make sure parent is not already set
        elseif (! $this->_Parent instanceof IObject && ! $this->_Parent instanceof IContainer && ! $this->_Parent instanceof IObjectStorage) {

            // Update parent
            $this->_Parent = $Parent;
            return IDataMapper::UPDATED;
        }

        // Else dont update parent
        else
            return IDataMapper::ONESHOT;
    }

    /**
     * Clears parent of the current object.
     *
     * @access private
     * @internal For internal use only.
     *
     * @return int Returns a <code>DataMapper</code> status code.
     */
    final public function clearParent()
    {
        $this->_Parent = null;
        return IDataMapper::UPDATED;
    }

#############################################################################################
# ArrayAccess trait
#############################################################################################

    /**
     * Returns whether the requested index exists
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param mixed $index
     *            The index being checked.
     * @return bool <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    public function offsetExists($index)
    {
        return isset($this->_Storage[$index]);
    }

    /**
     * Returns the value at the specified index
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param mixed $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    public function offsetGet($index)
    {
        if (isset($this->_Storage[$index]))
            return $this->_Storage[$index];

        else
            trigger_error(sprintf('Undefined index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);

        return null;
    }

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     * @return void
     */
    public function offsetSet($index, $newval)
    {
        trigger_error(sprintf('Cannot modify readonly index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);
    }

    /**
     * Unsets the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     * @return void
     */
    public function offsetUnset($index)
    {
        trigger_error(sprintf('Cannot modify readonly index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);
    }

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return int The number of public properties in the ArrayObject.
     */
    public function count()
    {
        return count($this->_Storage);
    }

#############################################################################################
# IteratorAggregate trait
#############################################################################################

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \RecursiveArrayIterator An instance implementing <code>Iterator</code>.
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this->_Storage);
    }

#############################################################################################
# Factory Trait
#############################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createURIString')
        );
    }

    /**
     * Create a URI string from individual URI components.
     *
     * @param array $Parts
     *            Parts generated by <code>IURI::parse() / parse_url</code>.
     * @return string Generated URI. Returns empty string on failure.
     */
    public static function createURIString(array $Parts)
    {
        // Create userinfo from parse_url data
        $getUserinfo = function ($Parts)
        {
            // Does userinfo exist?
            if (isset($Parts['userinfo'])) {
                return $Parts['userinfo'];
            }

            // Does user and pass exits?
            elseif (isset($Parts['user']) && isset($Parts['pass'])) {
                // Format userinfo
                return "$Parts[user]:$Parts[pass]";
            }

            // Does user exist?
            elseif (isset($Parts['user'])) {
                // Format userinfo
                return $Parts['user'];
            }

            // Does only pass exist?
            elseif (isset($Parts['pass'])) {
                return "anonymous:$Parts[pass]";
            }

            // Default
            return '';
        };

        // Gather URI components
        $scheme   = isset($Parts['scheme']) ? $Parts['scheme'] : '';
        $userinfo = $getUserinfo($Parts);
        $host     = isset($Parts['host']) ? $Parts['host'] : '';
        $port     = isset($Parts['port']) ? $Parts['port'] : '';
        $path     = isset($Parts['path']) ? $Parts['path'] : '';

        if (isset($Parts['query'])) {
            $query = is_array($Parts['query'])
                ? http_build_query($Parts['query'])
                : (is_string($Parts['query'])
                    ? $Parts['query']
                    : ''
                );
        }

        $fragment = isset($Parts['fragment'])
            ? $Parts['fragment']
            : '';

        // Build URI
        $return = '';

        // scheme
        if (! empty($scheme))
            $return .= "$scheme:";

        // Authority
        if (! empty($host))
            $return .= '//' . (! empty($userinfo) ? "$userinfo@" : '') . $host . (! empty($port) ? ":$port" : '');

        // path
        if (! empty($path))
            $return .= $path;

        // query
        if (! empty($query))
            $return .= "?$query";

        // fragment
        if (! empty($fragment))
            $return .= "#$fragment";

        // Done
        return $return;
    }

#############################################################################################
# URI trait
#############################################################################################

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$URI</code> are not a strings
     *
     * @param string|IURI $URI
     *            Universal Resouce Indicator.
     * @param \BLW\Type\IURI $BaseURI
     *            Base URI to resolve current URI against.
     */
    public function __construct($URI, IURI $BaseURI = null)
    {
        // Build parts from string
        if (is_string($URI) ?: is_callable(array(
            $URI,
            '__toString'
        ))) {

            $this->_Storage = !! $BaseURI
                ? $this->parse(strval($URI), iterator_to_array($BaseURI))
                : $this->parse(strval($URI));
        }

        // Invalid URI
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Returns an URI regex.
     *
     * @param string $Name
     *            Name of regex:
	 *
     * <ul>
     * <li><b>scheme</b>: URI scheme</li>
     * <li><b>hier-part</b>: absolute path and userinfo</li>
     * <li><b>relative-part</b>: relative path and userinfo</li>
     * <li><b>query</b>: query string (part after `?`)</li>
     * <li><b>fragment</b>: fragment (part after #)</li>
     * <li><b>authority</b>: URI authority (userinfo, host, port)</li>
     * <li><b>path-abempty</b>: URI path (type 1)</li>
     * <li><b>path-absolute</b>: URI path (type 2)</li>
     * <li><b>path-rootless</b>: URI path (type 3)</li>
     * <li><b>path-noscheme</b>: URI path (type 4)</li>
     * <li><b>path-empty</b>: URI path (type 5)</li>
     * <li><b>userinfo</b>: username / pass / etc</li>
     * <li><b>ipv6address</b>: see rfc3986</li>
     * <li><b>ipv4address</b>: see rfc3986</li>
     * </ul>
	 *
     * @return string PRCRE expression.
     */
    public static function getRegex($Name = 'uri-spec')
    {
        static $cache;

        if (isset($cache[$Name]))
            return $cache[$Name];

        // #################################################################################
        //
        // pct-encoded := "%" HEXDIG HEXDIG
        // unreserved  := ALPHA / DIGIT / "-" / "." / "_" / "~"
        // reserved    := gen-delims / sub-delims
        // gen-delims  := "#" / "/" / ":" / "?" / "@" / "[" / "]"
        // sub-delims  := "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
        // pchar       := unreserved / pct-encoded / sub-delims / ":" / "@"

        $pct_encoded = '\x25[0-9A-Fa-f][0-9A-Fa-f]';
        $unreserved  = '[\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a\x7e\p{L}]';
        $gen_dims    = '[\x23\x2f\x3a\x3f]';
        $sub_delims  = '[\x21\x24\x26-\x2c\x3b\x3d\x40\x5b\x5d]';
        $reserved    = '[\x21\x23\x24\x26-\x2c\x2f\x3a\x3b\x3d\x3f\x40\x5b\x5d]';
        $pchar       = "[\\x21\\x24\\x26-\\x2e\\x30-\\x3b\\x3d\\x40-\\x5a\\x5f\\x61-\\x7a\\x7e\\p{L}]|$pct_encoded";

        // ###############################################################################
        //
        // segment       := *pchar
        // segment-nz    := 1*pchar
        // segment-nz-nc := 1*(unreserved / pct-encoded / sub-delims / "@")
        //                  ; non-zero-length segment without any colon ":"

        $segment       = "(?:$pchar)*";
        $segment_nz    = "(?:$pchar)+";
        $segment_nz_nc = "(?:$unreserved|$pct_encoded|$sub_delims|\\x40)+";

        // ###############################################################################
        //
        // path-abempty  := *( "/" segment )
        // path-absolute := "/" [ segment-nz *( "/" segment ) ]
        // path-noscheme := segment-nz-nc *( "/" segment )
        // path-rootless := segment-nz *( "/" segment )
        // path-empty    := 0<pchar>

        $path_abempty  = "(?P<path_abempty>(?:\\x2f$segment)*)";
        $path_absolute = "(?P<path_absolute>\\x2f(?:$segment(?:\\x2f$segment)*)?)";
        $path_noscheme = "(?P<path_noscheme>$segment_nz_nc(?:\\x2f$segment)*)";
        $path_rootless = "(?P<path_rootless>$segment_nz(?:\\x2f$segment)*)";
        $path_empty    = '(?P<path_empty>)';

        // ################################################################################
        //
        // IP-literal  := "[" (IPv6address / IPvFuture) "]"
        //
        // IPvFuture   := "v" 1*HEXDIG "." 1*(unreserved / sub-delims / ":")
        //
        // IPv6address := 6(h16 ":") ls32
        //                / "::" 5(h16 ":") ls32
        //                / [h16] "::" 4(h16 ":") ls32
        //                / [*1(h16 ":") h16] "::" 3(h16 ":") ls32
        //                / [*2( h16 ":") h16] "::" 2(h16 ":") ls32
        //                / [*3(h16 ":") h16] "::" h16 ":" ls32
        //                / [*4(h16 ":") h16] "::" ls32
        //                / [*5(h16 ":") h16] "::" h16
        //                / [*6(h16 ":") h16] "::"
        //
        // IPv4address  := dec-octet "." dec-octet "." dec-octet "." dec-octet
        //
        // h16          := 1*4HEXDIG
        // ls32         := (h16 ":" h16) / IPv4address
        // dec-octet    := DIGIT ; 0-9
        //                 / %x31-39 DIGIT ; 10-99
        //                 / "1" 2DIGIT ; 100-199
        //                 / "2" %x30-34 DIGIT ; 200-249
        //                 / "25" %x30-35 ; 250-255

        $dec_octet   = '(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])';
        $IPv4Address = "(?P<IPv4Address>$dec_octet\\x2e$dec_octet\\x2e$dec_octet\\x2e$dec_octet)";

        $h16         = '[0-9A-Fa-f]{1,4}';
        $ls32        = "(?:$h16\\x3a$h16|$IPv4Address)";

        $IPv6Address = "(?P<IPv6Address>" . "(?:$h16\\x3a){6}$ls32|" . "\\x3a\\x3a(?:$h16\\x3a){5}$ls32|" . "(?:$h16)?\\x3a\\x3a(?:$h16\\x3a){5}$ls32|" . "(?:(?:$h16\\x3a){0,1}$h16)?\\x3a\\x3a(?:$h16\\x3a){3}$ls32|" . "(?:(?:$h16\\x3a){0,2}$h16)?\\x3a\\x3a(?:$h16\\x3a){2}$ls32|" . "(?:(?:$h16\\x3a){0,3}$h16)?\\x3a\\x3a(?:$h16\\x3a){1}$ls32|" . "(?:(?:$h16\\x3a){0,4}$h16)?\\x3a\\x3a$ls32|" . "(?:(?:$h16\\x3a){0,5}$h16)?\\x3a\\x3a$h16|" . "(?:(?:$h16\\x3a){0,6}$h16)?\\x3a\\x3a" . ")";

        $IPvFuture   = "v[0-9A-Fa-f]+\\x2e(?:$unreserved|$sub_delims|\\x3a)";

        $IPvLiteral  = "\\x5b(?:$IPv6Address|$IPvFuture)\\x5d";

        // ##############################################################################
        //
        // reg-name := *(unreserved / pct-encoded / sub-delims)

        $reg_name = "(?:$unreserved|$pct_encoded|$sub_delims)*";

        // ##############################################################################
        //
        // authority := [userinfo "@"] host [":" port]
        // userinfo  := *(unreserved / pct-encoded / sub-delims / ":")
        // host      := IP-literal / IPv4address / reg-name
        // port      := *DIGIT

        $userinfo  = "(?P<userinfo>(?:$unreserved|$pct_encoded|$sub_delims|\\x3a)*)";
        $host      = "(?P<host>$IPvLiteral|$IPv4Address|$reg_name)";
        $port      = "(?P<port>[0-9]*)";
        $authority = "(?:$userinfo\\x40)?$host(?:\\x3a$port)?";

        // ##############################################################################
        //
        // scheme       := ALPHA *(ALPHA / DIGIT / "+" / "-" / ".")
        // hier-part    := "//" authority path-abempty
        //                 / path-absolute
        //                 / path-rootless
        //                 / path-empty
        //
        // relative-part := "//" authority path-abempty
        //                  / path-absolute
        //                  / path-noscheme
        //                  / path-empty
        // query        := *(pchar / "/" / "?")
        // fragment     := *(pchar / "/" / "?")

        $scheme        = '(?P<scheme>[A-Za-z][\x2b\x2d\x2e0-9A-Za-z]*)';
        $hier_part     = "(?:(?:\\x2f\\x2f$authority$path_abempty)|$path_absolute|$path_rootless|$path_empty)";
        $relative_part = "(?:(?:\\x2f\\x2f$authority$path_abempty)|$path_absolute|$path_noscheme|$path_empty)";
        $query         = "(?P<query>(?:$pchar|\\x2f|\\x3f)*)";
        $fragment      = "(?P<fragment>(?:$pchar|\\x2f|\\x3f)*)";

        // ##############################################################################
        //
        // URI-reference := URI / relative-ref
        //
        // URI := scheme ":" hier-part ["?" query] ["#" fragment]
        //
        // relative-ref := relative-part ["?" query] ["#" fragment]

        $URI          = "$scheme\\x3a$hier_part(?:\\x3f$query)?(?:\\x23$fragment)?";
        $relative_ref = "$relative_part(?:\\x3f$query)?(?:\\x23$fragment)?";
        $URI_spec     = "(?J)(?P<URI>$URI|$relative_ref)";

        // ##################################################################################
        //
        // Cache results

        $cache = array(
            'uri-spec'      => $URI_spec,

            'scheme'        => $scheme,
            'hier-part'     => $hier_part,
            'relative-part' => $relative_part,
            'query'         => $query,
            'fragment'      => $fragment,

            'authority'     => $authority,
            'path-abempty'  => $path_abempty,
            'path-absolute' => $path_absolute,
            'path-rootless' => $path_rootless,
            'path-noscheme' => $path_noscheme,
            'path-empty'    => $path_empty,

            'userinfo'      => $userinfo,
            'host'          => $host,
            'port'          => $port,

            'reg-name'      => $reg_name,
            'ipv4address'   => $IPv4Address,
            'ipv6address'   => $IPv6Address
        );

        return @$cache[$Name];
    }

    /**
     * Resolves dot segments in a path.
     *
     * <h3>Introduction</h3>
     *
     * <p>This function takes a valid url path and nomalizes it into
     * the simplest form possible.</p>
     *
     * <hr>
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$path</code> is not a string or is empty.
     *
     * @param string $Path
     *            path to normalize.
     * @return string Normailized path.
     */
    public static function removeDotSegments($Path)
    {
        // Path is a string
        if (is_string($Path) ?: is_callable(array(
            $Path,
            '__toString'
        ))) {

            // Make sure path is a string
            $Path = strval($Path);

            // Is path empty?
            if (empty($Path))
                // Done
                return '';

            // Does path start with "/"
            $isAbsolute = substr($Path, 0, 1) == '/';

            // Does path end with "/" or "/." or "/.."
            $isTrailingSlash = substr($Path, -1) == '/' || substr($Path, -2) == '/.' || substr($Path, -3) == '/..';

            // Split path into segments
            $Segments = preg_split('!/!', $Path, -1, PREG_SPLIT_NO_EMPTY);

            // Go through parts and resolve dots ("." & "..")
            for ($up = 0, $i = count($Segments) - 1; $i >= 0; $i--) {

                // Remove empty spaces
                $Segments[$i] = trim($Segments[$i]);

                // Part is a single dot or empty
                if (! $Segments[$i] || $Segments[$i] == '.') {
                    // Remove it
                    unset($Segments[$i]);
                }

                // Part is a double dot
                elseif ($Segments[$i] == '..') {
                    // Remove it
                    unset($Segments[$i]);

                    // Move up
                    $up ++;
                }

                // Part is a directory / file
                else {

                    // did we move up?
                    if ($up) {
                        // Remove it
                        unset($Segments[$i]);

                        // move down
                        $up--;
                    }
                }
            }

            // Recreate path
            $Path = implode('/', $Segments);

            // Restore trailing slash
            if (! empty($Path) && $isTrailingSlash)
                $Path .= '/';

            // Restore starting slash
            if ($isAbsolute)
                $Path = '/' . $Path;

            // Done
            return $Path;
        }

        // path is not a string
        else
            throw new InvalidArgumentException(0);

        // Done`
        return '';
    }

    /**
     * Retrieves TLD from an internet domain name.
     *
     * @param string $Domain
     *            host to parse
     * @return string false Level Domain. Returns <code>FALSE</code> on failure.
     */
    public static function parseTLD($Domain)
    {
        $TLD    = '';
        $Domain = strtolower(substr($Domain, -16));
        $Parts  = (array) explode('.', $Domain);

        // Loop through last 2 domain parts
        // ----------------------------------------
        // Note matches any 2 combinations of TLDs
        //
        foreach (array_splice($Parts, -2) as $Part) {

            // If part is a valid TLD add it
            if (isset(self::$_TLDs[$Part]))
                $TLD .= ".$Part";
        }

        // Remove preceeding slash or return false on empty string
        return substr($TLD, 1) ?: false;
    }

    /**
     * Parse a url into it's various components
     *
     * @throws \BLW\Model\InvalidArgumentException If:
     *         <ul>
     *         <li>$URI is not a string.</li>
     *         <li>$baseURI is not an array containing `scheme`, `path` and `query`.
     *         </ul>
     *
     * @link http://tools.ietf.org/html/rfc3986#section-5.2.2 RFC3986
     *
     * @param string $URI
     *            URI to parse.
     * @param array $baseURI
     *            Parts of base URI to use to resolve current URI.
     * @return array Parsed parts
     *         <ul>
     *         <li><b>scheme</b>: http / ftp / etc</li>
     *         <li><b>userinfo</b>:</li>
     *         <li><b>host</b>: domain name / ip address / ip literal</li>
     *         <li><b>port</b>: as a string</li>
     *         <li><b>path</b>:</li>
     *         <li><b>query</b>: part after `?`</li>
     *         <li><b>fragment</b>: part after `#`</li>
     *         <li><b>IPv4Address</b>: xxx.xxx.xxx of host if available</li>
     *         <li><b>IPv6Address</b>: xxxx:xxxx:xxxx:xxxx:xxxx</li>
     *         </ul>
     */
    public static function parse($URI, array $baseURI = array('scheme' => '', 'path' => '', 'query' => array()))
    {
        // Default return value
        $return = array();

        // Validate $baseURI
        switch (true) {

            case ! isset($baseURI['scheme']):
            case ! isset($baseURI['path']):
            case ! isset($baseURI['query']):

            case ! is_string($baseURI['scheme']):
            case ! is_string($baseURI['path']):
            case ! is_array($baseURI['query']):

                throw new InvalidArgumentException(1);
                return $return;
        }

        // Is URI a string?
        if (is_string($URI) ?  : is_callable(array(
            $URI,
            '__toString'
        ))) {

            // Run regex
            if (preg_match('!^' . self::getRegex() . '$!', $URI, $m)) {

                // Does scheme exist?
                if (! empty($m['scheme'])) {

                    // scheme
                    $return['scheme']      = $m['scheme'];

                    // Authority
                    $return['userinfo']    = isset($m['userinfo']) ? $m['userinfo'] : '';
                    $return['host']        = isset($m['host']) ? $m['host'] : '';
                    $return['port']        = isset($m['port']) ? $m['port'] : '';
                    $return['IPv4Address'] = $m['IPv4Address'];
                    $return['IPv6Address'] = $m['IPv6Address'];

                    // path
                    $path           = strval(@$m['path_abempty'] ?: @$m['path_absolute'] ?: @$m['path_rootless'] ?: @$m['path_empty']); /// hehehe  eviiiil!!!
                    $return['path'] = self::removeDotSegments($path, false);

                    // query
                    parse_str(isset($m['query']) ? $m['query'] : '', $return['query']);
                }

                // scheme doesn't exist
                else {

                    // Does authority exist?
                    if (! empty($m['host'])) {

                        // Authority
                        $return['userinfo']    = isset($m['userinfo']) ? $m['userinfo'] : '';
                        $return['host']        = isset($m['host']) ? $m['host'] : '';
                        $return['port']        = isset($m['port']) ? $m['port'] : '';
                        $return['IPv4Address'] = $m['IPv4Address'];
                        $return['IPv6Address'] = $m['IPv6Address'];

                        // path
                        $return['path'] = self::removeDotSegments($m['path_abempty'], false);

                        // query
                        parse_str(isset($m['query']) ? $m['query'] : '', $return['query']);
                    }

                    // Authority doesn't exist
                    else {

                        // Is path empty?
                        $path = strval(@$m['path_absolute'] ?: @$m['path_noscheme'] ?: @$m['path_empty']); /// hehehe  eviiiil!!!

                        if (empty($path)) {

                            // Set path to base path
                            $return['path'] = strval($baseURI['path']);

                            // Is query set?
                            if (! empty($m['query']))
                                parse_str(isset($m['query']) ? $m['query'] : '', $return['query']);

                            // Use base query
                            else
                                $return['query'] = $baseURI['query'];
                        }

                        // path is set
                        else {

                            // Does path start with "/"
                            if (substr($path, 0, 1) == '/')
                                $return['path'] = self::removeDotSegments($path, false);

                                // path is relative, Merge with base
                            else {
                                $base = substr($baseURI['path'], 0, strrpos($baseURI['path'], '/') + 1);
                                $return['path'] = self::removeDotSegments($base . $path, false);
                            }

                            // query
                            parse_str(isset($m['query']) ? $m['query'] : '', $return['query']);
                        }

                        // Authority
                        $return['userinfo']    = isset($baseURI['userinfo']) ? strval($baseURI['userinfo']) : '';
                        $return['host']        = isset($baseURI['host']) ? strval($baseURI['host']) : '';
                        $return['port']        = isset($baseURI['port']) ? strval($baseURI['port']) : '';
                        $return['IPv4Address'] = isset($baseURI['IPv4Address']) ? strval($baseURI['IPv4Address']) : '';
                        $return['IPv6Address'] = isset($baseURI['IPv6Address']) ? strval($baseURI['IPv6Address']) : '';
                    } // End path is set

                    // scheme
                    $return['scheme'] = strval($baseURI['scheme']);

                } // End scheme not Exists

                // fragment
                $return['fragment'] = isset($m['fragment']) ? $m['fragment'] : '';

                // TLD
                if (! empty($return['host']) && empty($return['IPv4Address']) && empty($return['IPv4Address'])) {
                    $return['TLD'] = self::parseTLD($return['host']);
                }
            }

            // Regex fail
            else {
                // Copy base URI
                $return = $baseURI;

                // Remove fragment
                $return['fragment'] = '';
            }
        }

        // Invalid URI
        else
            throw new InvalidArgumentException(0);

        // Done
        return array_merge(self::$_Default, $return);
    }

    /**
     * Parse a second URI using current URI as a base.
     *
     * @see \BLW\Type\IURI::parse() IURI::parse()
     * @see \BLW\Type\IURI::createURIString() IURI::createURIString()
     *
     * @param string $URI
     *            URI to parse.
     * @param int $flags
     *            Relove flags.
	 *
     * <ul>
     * <li><b>IURI::AS_STRING</b>: Return a string of uri (IURI::createString())
     * <li><b>IURI::AS_ARRAY</b>: Return an array of uri parts (IURI::parse())
     * </ul>
	 *
     * @return array string parts. Returns <code>null</code> in case of error.
     */
    public function resolve($URI, $flags = IURI::AS_STRING)
    {
        // Return a string (Default)
        if ($flags & IURI::AS_STRING) {
            return $this->createURIString($this->parse($URI, $this->_Storage));
        }

        // Return an array
        elseif ($flags & IURI::AS_ARRAY) {
            return $this->parse($URI, $this->_Storage);
        }

        // Invalid flags
        else
            throw new InvalidArgumentException(0);

        // Default
        return false;
    }

    /**
     * Validates a URI.
     *
     * @return bool <code>TRUE</code> if valid. <code>FALSE</code> otherwise.
     */
    public function isValid()
    {
        $scheme    = $this->_Storage['scheme'];
        $userinfo  = $this->_Storage['userinfo'];
        $host      = $this->_Storage['host'];
        $port      = $this->_Storage['port'];
        $path      = $this->_Storage['path'];
        $query     = http_build_query($this->_Storage['query']);
        $fragment  = $this->_Storage['fragment'];
        $Total     = 0;

        // Check path / host
        if (empty($path) && empty($host))
            return false;

        // scheme
        if (($l = strlen($scheme)) > 31)
            return false;

        $Total += $l;

        // host
        if (($l = strlen($host)) > 255)
            return false;

        $Total += $l;

        // port is < 0 / port > 65535 / port not empty
        if (is_numeric($port) ? (intval($port) < 0 && intval($port) > 65535) : ! empty($port))
            return false;

        $Total += strlen($port);

        // path
        if (($l = strlen($path)) > 2048)
            return false;

        $Total += $l;

        // Total
        $Total += strlen($query) + strlen($fragment);

        if ($Total > 2064)
            return false;

        // Done
        return true;
    }

    /**
     * Validates an absolute URI.
     *
     * @return bool <code>TRUE</code> if absolute. <code>FALSE</code> otherwise.
     */
    public function isAbsolute()
    {
        return ! empty($this->_Storage['scheme']) && (! empty($this->_Storage['host']) || ! empty($this->_Storage['path']));
    }

    /**
     * All objects must have a string representation.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-5.3 RFC3986
     *
     * @return string String value of object.
     */
    public function __toString()
    {
        return $this->createURIString($this->_Storage);
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return md5(strval($this));
    }

#############################################################################################

}

return true;
