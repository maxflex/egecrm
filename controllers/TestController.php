<?php

	// Контроллер
	class TestController extends Controller
    {
        public $defaultAction = "test";
        // Папка вьюх
        protected $_viewsFolder = "test";


    //        public function beforeAction()
    //        {
    //            ini_set("display_errors", 1);
    //            error_reporting(E_ALL);
    //        }
    	
		public function actionTimeTest()
		{
			$Group = Group::find(74);
			preType($Group);
		}
    	
    	public function actionSmsSendNew()
    	{
	    	$phones = [79015171164, 79015198446, 79015789505, 79017571009, 79030097257, 79030109444, 79030118820, 79030121122, 79030126464, 79030132400, 79030159234, 79031029963, 79031111139, 79031128660, 79031134693, 79031184390, 79031206683, 79031207908, 79031212974, 79031242050, 79031280209, 79031298263, 79031301755, 79031317590, 79031365309, 79031397979, 79031452927, 79031483395, 79031487623, 79031520225, 79031526777, 79031551428, 79031559352, 79031624142, 79031660154, 79031662570, 79031677607, 79031716226, 79031775608, 79031837849, 79031852998, 79031883440, 79031905859, 79031922196, 79031934188, 79031956768, 79032008116, 79032015798, 79032017375, 79032044901, 79032093137, 79032145307, 79032211000, 79032219905, 79032280807, 79032308342, 79032333472, 79032348789, 79032408165, 79032467062, 79032479995, 79032539578, 79032690596, 79032760217, 79032763041, 79032789694, 79032836075, 79032883388, 79032891750, 79032983420, 79035003978, 79035048718, 79035097471, 79035162174, 79035184209, 79035229683, 79035286509, 79035304456, 79035337022, 79035355017, 79035437626, 79035466771, 79035499079, 79035501956, 79035529495, 79035534181, 79035541906, 79035574232, 79035595808, 79035624153, 79035628180, 79035664629, 79035683072, 79035710177, 79035793260, 79035827405, 79035855523, 79035889356, 79035892056, 79035892655, 79035913218, 79035960469, 79035963646, 79035972704, 79036177339, 79036181841, 79036194747, 79036214385, 79036241634, 79036695222, 79036730194, 79036859817, 79036886506, 79037000124, 79037003633, 79037076793, 79037115745, 79037125095, 79037148579, 79037244521, 79037247709, 79037257634, 79037258474, 79037267669, 79037287201, 79037292348, 79037308014, 79037312718, 79037319013, 79037413040, 79037430346, 79037458899, 79037479654, 79037503773, 79037513650, 79037551915, 79037555786, 79037616291, 79037623862, 79037636683, 79037683648, 79037690557, 79037727923, 79037779277, 79037787118, 79037793585, 79037880672, 79037902379, 79037909044, 79037932231, 79037971690, 79037983013, 79037986098, 79039617783, 79039655331, 79039670754, 79039681599, 79039693450, 79039695790, 79039726095, 79055193093, 79055305552, 79055308132, 79055388660, 79055446193, 79055467676, 79055541730, 79055563806, 79055731848, 79055810193, 79055817156, 79056101297, 79057012279, 79057147619, 79057283344, 79057355149, 79057379592, 79057381188, 79057388418, 79057391344, 79057435640, 79057439886, 79057463039, 79057495559, 79057516107, 79057578213, 79057626384, 79057700664, 79057719625, 79057745163, 79057839367, 79057879670, 79057948521, 79060549000, 79060581863, 79060627150, 79060646372, 79060753670, 79060823492, 79060877744, 79066524967, 79067044203, 79067091553, 79067130106, 79067179333, 79067295161, 79067379635, 79067383574, 79067384327, 79067404615, 79067432563, 79067502117, 79067541824, 79067806603, 79091562261, 79091604286, 79091641724, 79091643132, 79096353548, 79096383342, 79096417846, 79096422248, 79096429044, 79096487464, 79096511638, 79096590994, 79096597785, 79096618725, 79096660223, 79096782372, 79096872345, 79096991604, 79099160763, 79099233824, 79099302494, 79099315472, 79099321838, 79099406073, 79099614102, 79099630676, 79099887069, 79099935672, 79099946022, 79099989390, 79102589907, 79102916464, 79103289280, 79104004066, 79104014545, 79104020871, 79104046141, 79104060899, 79104061189, 79104072506, 79104077077, 79104137001, 79104192809, 79104205635, 79104244513, 79104262079, 79104262271, 79104278253, 79104344619, 79104388165, 79104388630, 79104415246, 79104442206, 79104467725, 79104480955, 79104500808, 79104524120, 79104542638, 79104543758, 79104552348, 79104566812, 79104596765, 79104614000, 79104627605, 79104650113, 79104651364, 79104707759, 79104721592, 79104746244, 79104769360, 79104812781, 79104832547, 79104847261, 79104916884, 79104921174, 79104935216, 79105425771, 79106020095, 79107424823, 79108351722, 79112127195, 79118100003, 79125303623, 79129356541, 79139768877, 79140850709, 79142267946, 79148636454, 79150088472, 79150222165, 79150525374, 79150555119, 79150614289, 79150615528, 79150666854, 79150765776, 79150820800, 79150855749, 79150904041, 79150964312, 79150985800, 79151002791, 79151098830, 79151253466, 79151296609, 79151361349, 79151393863, 79151451505, 79151540444, 79151646562, 79151789676, 79151801448, 79151851434, 79151957121, 79151968303, 79151997332, 79152011177, 79152020240, 79152085586, 79152318361, 79152334684, 79152390915, 79152432333, 79152522223, 79152807174, 79152808760, 79152815993, 79152818768, 79152857047, 79152901576, 79153021212, 79153058149, 79153064493, 79153079260, 79153128546, 79153186834, 79153195281, 79153272754, 79153316647, 79153379796, 79153383640, 79153394279, 79153504714, 79153507037, 79153571505, 79153586330, 79153591379, 79153674760, 79153681895, 79153769587, 79153984000, 79154160091, 79154162811, 79154223250, 79154284357, 79154306555, 79154325993, 79154333137, 79154447377, 79154463652, 79154467256, 79154468303, 79154509379, 79154510429, 79154600757, 79154758390, 79154880432, 79154896971, 79154929708, 79157813195, 79160107792, 79160160167, 79160289670, 79160338703, 79160368628, 79160380880, 79160446718, 79160558241, 79160559992, 79160573850, 79160577457, 79160577641, 79160612977, 79160643110, 79160658084, 79160658533, 79160703690, 79160760788, 79160790625, 79160828879, 79160921223, 79160933218, 79160985953, 79161023316, 79161062383, 79161137082, 79161153683, 79161181522, 79161183193, 79161218496, 79161241412, 79161283860, 79161291441, 79161309008, 79161315020, 79161404053, 79161412552, 79161448174, 79161516474, 79161590684, 79161592658, 79161597905, 79161746942, 79161765287, 79161808340, 79161812654, 79161813422, 79161964341, 79161988556, 79162016601, 79162109098, 79162134426, 79162139964, 79162173544, 79162191996, 79162211579, 79162258438, 79162263201, 79162278150, 79162380574, 79162407378, 79162421842, 79162440454, 79162454301, 79162477560, 79162505889, 79162526051, 79162533323, 79162592763, 79162624801, 79162666328, 79162759733, 79162760820, 79162789750, 79163000885, 79163012432, 79163014715, 79163032333, 79163093000, 79163168958, 79163235303, 79163291961, 79163293336, 79163295204, 79163343149, 79163424043, 79163472741, 79163485569, 79163553396, 79163610195, 79163719807, 79163882589, 79163904565, 79163975127, 79164007997, 79164011088, 79164262908, 79164332061, 79164391911, 79164414983, 79164422468, 79164425942, 79164554796, 79164608588, 79164614828, 79164773034, 79164938122, 79164969109, 79164979044, 79165018162, 79165047850, 79165066991, 79165076078, 79165091748, 79165114750, 79165123665, 79165136123, 79165159238, 79165192951, 79165214557, 79165226001, 79165242543, 79165253332, 79165330551, 79165397820, 79165432494, 79165457063, 79165471677, 79165506688, 79165537583, 79165605060, 79165608526, 79165608924, 79165660056, 79165704936, 79165713505, 79165781800, 79165786388, 79165850572, 79165858521, 79165863323, 79165877865, 79165888832, 79165890139, 79165906687, 79165951076, 79166005708, 79166006640, 79166013305, 79166036773, 79166036796, 79166046904, 79166067172, 79166084301, 79166086808, 79166098307, 79166131556, 79166174613, 79166181908, 79166203281, 79166206815, 79166221134, 79166238860, 79166240383, 79166254500, 79166267148, 79166270956, 79166297753, 79166377366, 79166397588, 79166409383, 79166422286, 79166454352, 79166501486, 79166520033, 79166531449, 79166593048, 79166621612, 79166704852, 79166731620, 79166760993, 79166765265, 79166778809, 79166790177, 79166811462, 79166844078, 79166852819, 79166858351, 79166878942, 79166884662, 79166885235, 79166885509, 79166910902, 79166944822, 79166951484, 79166961303, 79166984352, 79167072981, 79167098136, 79167112092, 79167126273, 79167173833, 79167186216, 79167272093, 79167300122, 79167355145, 79167387332, 79167436263, 79167437499, 79167552423, 79167554003, 79167618108, 79167744448, 79167833659, 79167858802, 79167867979, 79167868788, 79167885099, 79167954318, 79168013863, 79168077247, 79168090699, 79168091902, 79168180376, 79168232929, 79168233610, 79168272091, 79168289690, 79168401289, 79168435938, 79168482606, 79168559535, 79168680016, 79168698818, 79168705543, 79168779279, 79168831384, 79168879477, 79169036146, 79169036677, 79169037088, 79169076876, 79169093083, 79169109337, 79169113605, 79169140394, 79169140587, 79169159809, 79169216093, 79169258924, 79169280672, 79169281355, 79169366423, 79169410811, 79169414079, 79169430325, 79169516878, 79169525690, 79169525854, 79169527359, 79169535044, 79169557216, 79169588877, 79169616107, 79169633164, 79169711936, 79169717693, 79169797287, 79169822580, 79169900555, 79169906636, 79169948884, 79175004040, 79175007315, 79175099234, 79175193800, 79175196070, 79175199820, 79175207482, 79175315253, 79175346591, 79175434149, 79175445370, 79175451410, 79175566232, 79175578557, 79175621479, 79175633836, 79175708641, 79175925777, 79175996052, 79186928395, 79191004370, 79191043393, 79191094008, 79197287539, 79197767384, 79199640339, 79199708651, 79208513607, 79216415799, 79232684443, 79250015060, 79250033445, 79250038826, 79250045002, 79250056098, 79250160442, 79250215623, 79250357506, 79250357519, 79250377107, 79250476507, 79250530869, 79250651421, 79250659717, 79250668818, 79250706531, 79250758189, 79251254922, 79251420803, 79251522207, 79251671372, 79251802944, 79251816575, 79251879210, 79251946021, 79252158393, 79252180380, 79252231879, 79252253391, 79252343911, 79252397451, 79252742380, 79252965374, 79253024660, 79253029142, 79253161407, 79253276797, 79253585719, 79253594865, 79253613099, 79253709094, 79253785088, 79254067777, 79254770748, 79255054395, 79255067730, 79255069215, 79255077507, 79255087228, 79255092716, 79255172782, 79255175577, 79255200668, 79255222738, 79255226297, 79255303025, 79255319518, 79255447841, 79255496208, 79255969060, 79256138803, 79256230782, 79256292418, 79256313183, 79256359390, 79257043822, 79257081712, 79257088570, 79257110396, 79257138353, 79257151547, 79257208429, 79257293307, 79257318384, 79257406639, 79257489610, 79257516263, 79257667754, 79257730188, 79257918434, 79258021979, 79258300976, 79258407278, 79258423330, 79258500037, 79258717275, 79258767926, 79258831840, 79258832909, 79259047984, 79259067219, 79259163960, 79259916541, 79260191900, 79260477184, 79260597583, 79260601315, 79260609292, 79260624822, 79260671197, 79260701570, 79260945431, 79261083974, 79261093077, 79261201193, 79261229007, 79261264648, 79261498217, 79261531744, 79261532217, 79261552357, 79261594272, 79261692344, 79261781243, 79261804699, 79261860316, 79261861779, 79261890191, 79261901425, 79261956858, 79261981163, 79262005527, 79262005591, 79262030846, 79262077477, 79262082302, 79262100020, 79262154818, 79262168788, 79262210175, 79262270523, 79262284528, 79262307777, 79262349712, 79262440683, 79262467537, 79262653401, 79262731600, 79262752256, 79262767881, 79262786643, 79262809962, 79262945911, 79263013020, 79263126998, 79263151353, 79263165525, 79263173789, 79263176953, 79263180814, 79263187774, 79263187911, 79263205065, 79263257103, 79263288827, 79263297545, 79263323256, 79263397044, 79263415381, 79263550225, 79263554964, 79263737925, 79263743288, 79263822185, 79263879304, 79264000035, 79264064443, 79264120785, 79264291913, 79264343933, 79264372225, 79264391169, 79264407544, 79264518099, 79264634952, 79264812548, 79264948272, 79264991955, 79265208934, 79265237577, 79265238584, 79265281781, 79265296991, 79265380959, 79265381151, 79265390349, 79265398392, 79265418015, 79265447808, 79265461034, 79265488384, 79265509357, 79265550507, 79265688802, 79265721408, 79265763329, 79265821897, 79265864776, 79265872130, 79265897652, 79265917224, 79265942754, 79265975410, 79266029758, 79266075888, 79266100576, 79266164993, 79266176581, 79266247979, 79266279700, 79266321516, 79266323659, 79266352530, 79266368584, 79266388126, 79266430078, 79266458679, 79266676706, 79266721662, 79266769342, 79266770515, 79266907193, 79266912423, 79266918931, 79266958944, 79267033768, 79267087981, 79267118327, 79267142873, 79267226985, 79267238493, 79267269613, 79267289282, 79267610673, 79267790784, 79267914542, 79267939619, 79268040428, 79268112134, 79268115838, 79268237312, 79268289232, 79268302941, 79268405337, 79268421977, 79268427913, 79268461108, 79268607202, 79268923547, 79268942901, 79268976640, 79269021050, 79269031116, 79269035447, 79269039913, 79269107658, 79269122791, 79269173118, 79269222878, 79269374969, 79269534080, 79269647647, 79269709540, 79269715132, 79269723373, 79269792617, 79269828884, 79269873006, 79269900514, 79269942622, 79285387777, 79287194813, 79288812389, 79288823952, 79289186317, 79290392395, 79295089921, 79295180962, 79295281413, 79295426739, 79295535512, 79295851566, 79296045811, 79296078715, 79296272453, 79296357605, 79296415314, 79296431590, 79296508836, 79296567073, 79296591769, 79296776273, 79296936736, 79299053319, 79299372365, 79299498982, 79299922905, 79299923642, 79299930893, 79323050649, 79382000589, 79515200100, 79515461506, 79533352807, 79604178121, 79607257646, 79623636633, 79626454302, 79629003501, 79629245860, 79629271004, 79629276393, 79629342235, 79629374559, 79629418695, 79629665370, 79629697641, 79629732618, 79629768668, 79629787926, 79629819703, 79629927318, 79629938089, 79629940523, 79633235761, 79636147886, 79636268555, 79636287878, 79636333337, 79636388502, 79636575780, 79636594574, 79636807474, 79636883555, 79636908546, 79636995956, 79637122053, 79637123519, 79637123727, 79637151675, 79637201652, 79637611682, 79637627680, 79637858107, 79639219970, 79639276499, 79639666951, 79639719385, 79639779930, 79639906214, 79639921461, 79639925483, 79645157764, 79645162886, 79645169999, 79645232121, 79645342434, 79645731313, 79645836521, 79645936502, 79645955114, 79646204723, 79646223827, 79646401155, 79647043377, 79647052626, 79647054762, 79647169766, 79647282253, 79647624460, 79647726645, 79647754733, 79647802993, 79647891752, 79647919077, 79647939043, 79647993371, 79648717728, 79648989888, 79651040035, 79651149390, 79651208027, 79651243776, 79651416252, 79651662057, 79651844945, 79651878303, 79651997707, 79652030330, 79652114112, 79652292022, 79652614001, 79652643232, 79652670409, 79652778029, 79652886619, 79652959057, 79652979532, 79652982939, 79653088188, 79653177783, 79653318448, 79653363635, 79653444053, 79653543076, 79653853402, 79653994501, 79654023317, 79654141499, 79660091523, 79660202050, 79660273695, 79660324554, 79660375778, 79660711010, 79660719066, 79660886553, 79660990321, 79661102545, 79661162146, 79661313293, 79661400513, 79661940800, 79663164181, 79663197012, 79663395698, 79663853300, 79663860029, 79670164649, 79670263628, 79670349422, 79670443673, 79670595168, 79670661139, 79670896360, 79670993022, 79671014282, 79671091064, 79671389813, 79671514608, 79671523049, 79671562581, 79671615635, 79671656342, 79671869777, 79671913935, 79672061041, 79672085045, 79672373888, 79672541414, 79672604515, 79672613712, 79672686472, 79672798284, 79672910345, 79672922286, 79672932269, 79672938809, 79680249024, 79680626561, 79683247238, 79683264494, 79683517339, 79683541770, 79683750714, 79684092050, 79684204424, 79684375315, 79684649099, 79684729404, 79685020591, 79685047533, 79685048947, 79685070701, 79685302758, 79685310201, 79685316248, 79685340202, 79685466767, 79685471963, 79685539048, 79685654371, 79685871607, 79685907475, 79686028866, 79686121133, 79686792085, 79687032342, 79687240832, 79687334917, 79687750943, 79687937347, 79688049910, 79688063032, 79688185082, 79688300876, 79688456687, 79688782976, 79688870113, 79689247724, 79689260273, 79689264612, 79689327717, 79689521351, 79689649322, 79689686776, 79692757449, 79772528600, 79772606579, 79772611684, 79772669156, 79772671235, 79772706296, 79772792811, 79772957270, 79773115793, 79773301051, 79773394035, 79773492879, 79773633032, 79773923802, 79774740898, 79774779230, 79775172543, 79775191075, 79775214320, 79775779838, 79776043074, 79776178949, 79776542687, 79777198466, 79777284529, 79777381550, 79778105361, 79778262700, 79778844177, 79779270800, 79779454687, 79779655615, 79779661023, 79779863612, 79781306185, 79806396927, 79850451562, 79850506350, 79851151675, 79851218662, 79851303812, 79851382586, 79851459551, 79851512587, 79851521978, 79851618518, 79851703609, 79851759306, 79851955303, 79851988314, 79851992588, 79852003300, 79852108831, 79852142814, 79852224540, 79852228245, 79852240753, 79852251090, 79852266220, 79852268089, 79852292324, 79852299990, 79852316065, 79852350791, 79852364008, 79852380192, 79852520203, 79852593207, 79852600934, 79852607761, 79852625450, 79852647524, 79852672535, 79852695744, 79852714487, 79852782288, 79852961372, 79853010144, 79853046025, 79853053795, 79853054566, 79853135392, 79853438359, 79853441420, 79853617064, 79853852584, 79853876214, 79853878492, 79853992999, 79854101529, 79854108037, 79854129770, 79854247033, 79854449044, 79854481021, 79854544320, 79854618478, 79854653095, 79854770765, 79854873454, 79854924349, 79855740202, 79856249932, 79856655555, 79857248884, 79857272693, 79857299946, 79857320154, 79857491969, 79857633123, 79857649362, 79857667584, 79857683079, 79857690548, 79857708063, 79857721292, 79857731532, 79857733994, 79857742294, 79857767658, 79857798110, 79857814979, 79857825068, 79857855515, 79858043446, 79858072932, 79858266144, 79858392436, 79858462272, 79858558853, 79858628321, 79858697737, 79858950681, 79859061689, 79859064236, 79859065838, 79859118200, 79859146633, 79859220043, 79859238505, 79859512103, 79859581995, 79859679950, 79859704596, 79859738058, 79859777979, 79859804711, 79859904424, 79859925245, 79859928862, 79859932203, 79859956733, 79859970107, 79859991660, 79951993197, 79969142862, 79969769505, 79969793617, 79991124840, 79995249805, 79996675287, 79997681491, 79997731744, 79998022659, 79998041954, 79998185749, 79998289452, 79999724283, 79999899004, 994514870011];
	    	
	    	$offset = 0;
	    	$limit = 300; // отправка по 300 номеров
	    	
	    	$message = "ЕГЭ-Центр информирует: в центре на м.Тургеневская созданы новые группы подготовки к ЕГЭ в будни 18:40 и в выходные. Запись на курсы до 10 сентября! Подробнее по тел.: +7(495)646-85-92";
	    	
	    	$sent = 0;
	    	foreach(range($offset, count($phones) - 1) as $i) {
		    	SMS::send($phones[$i], $message);
		    	$sent++;
		    	if ($sent > ($limit - 1)) {
			    	break;
		    	}
	    	}

	    	preType($sent);
/*
			$remove = [];
			foreach($phones as $phone) {
				$query = "
					select * from contracts c
					join students s on c.id_student = s.id
					join requests r on r.id_student = s.id
					join representatives p on p.id = s.id_representative
					where (
							s.phone = '{$phone}' or s.phone2 = '{$phone}' or s.phone3 = '{$phone}' or 
							r.phone = '{$phone}' or r.phone2 = '{$phone}' or r.phone3 = '{$phone}' or 
							p.phone = '{$phone}' or p.phone2 = '{$phone}' or p.phone3 = '{$phone}' 
						  ) and c.year = 2016
				";
				
				$result = dbConnection()->query($query);
				if ($result->num_rows) {
					$remove[] = $phone;
				}
			}
			echo implode(', ', $remove);
*/
    	}
    	
    	public function actionMemcached()
		{
			preType(memcached()->get('testy'));
		}
    
		public function actionTest()
		{
			$t = TestStudent::findById(30);
			var_dump($t);
		}

		/**
		 * слияние таблиц Payments
		 */
		public function actionMergePayments()
		{
			dbConnection()->query("alter table `payments` add column entity_type varchar(255)");
			dbConnection()->query("update `payments` set entity_type = 'STUDENT'");
			dbConnection()->query("alter table `payments` change column id_student entity_id int unsigned");
			dbConnection()->query("insert into `payments` (entity_id, entity_type, id_status, id_type, id_user, sum, card_number, date, first_save_date, confirmed) ".
								  "select id_teacher, 'TEACHER', id_status, id_type, id_user, sum, card_number, date, first_save_date, confirmed ".
								  "from teacher_payments"
								 );
//			dbConnection()->query("drop table `teacher_payments`");
		}


		/**
         * Обновление статусов задач
         */
        public function actionUpdateTasksStatuses()
        {
            $Tasks = Task::findAll();
            /* @var $Tasks Group[] */
            foreach ($Tasks as $Task) {
                switch ($Task->id_status) {
                    case 2: // выполнено  => выгружен в гитхаб
                        $Task->id_status = 4;
                        break;
                    case 3: // требует доработки
                        $Task->id_status = 7;
                        break;
                    case 4: // Закрыто
                        $Task->id_status = 8;
                        break;
                }
                $Task->save('id_status');
            }
        }

		/**
		 * Проверка отправки смс при отмене уроков
		 */
		public function actionTestCancelLesson()
		{
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);

			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;

			// все отмененные завтрашние занятия
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled = 1 ",
				"group"		=> "id_group",
			]);

			$group_ids = [];
			foreach ($GroupSchedule as $GS) {
				$group_ids[] = $GS->id_group;
			}

			$Groups = Group::findAll([
				"condition" => "id IN (" . implode(",", $group_ids) . ")"
			]);

			foreach($Groups as $Group) {
				if ($Group->id_teacher) {
					$Teacher = Teacher::findById($Group->id_teacher);
					if ($Teacher) {
						foreach (Student::$_phone_fields as $phone_field) {
							$teacher_number = $Teacher->{$phone_field};
							if (!empty($teacher_number)) {
								$messages[] = [
									"type"      => "Учителю #" . $Teacher->id,
									"number" 	=> $teacher_number,
									"message"	=> CronController::_generateCancelledMessage($Group, $Teacher, $tomorrow),
								];
							}
						}
					}
				}
				foreach ($Group->students as $id_student) {
					$Student = Student::findById($id_student);
					if (!$Student) {
						continue;
					}

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> CronController::_generateCancelledMessage($Group, $Student, $tomorrow),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> CronController::_generateCancelledMessage($Group, $Student, $tomorrow),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
					//SMS::send($message['number'], $message['message'], ["additional" => 3]);
//					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

			Email::send("shamik1551@mail.ru", "СМС о отмененных занятиях завтра", $body);
		}

        /**
         * update user cache
         */
        public function actionClearUserCache()
        {
            User::updateCache();
        }

		/**
		 * Обновление кеша полей таблиц.
		 */
		public function actionClearColumnCache()
		{
			$Tables = dbConnection()->query("SHOW TABLES");

			while ($Table = $Tables->fetch_assoc())
			{
				$table_name = $Table["Tables_in_".DB_PREFIX."egecrm"];
				memcached()->delete($table_name."Columns");

				$Query = dbConnection()->query("SHOW COLUMNS FROM `".$table_name."`");
				$mysql_vars = [];
				while ($data = $Query->fetch_assoc()) {
					$mysql_vars[] = $data["Field"];
				}
				memcached()->set($table_name."Columns", $mysql_vars, 3600 * 24);
			}
		}

		/**
		 * Сравниние на предмет полного соответствия филиала и кабинета в журнале посещений и в расписании занятий,
		 * которые уже прошли.
		 */
		public function actionJournalScheduleConsistency()
		{
			/* VisitJournal[] несоответствующие элементы */
			$discrepancy = [];
			$checkCnt = 0;

			$GroupSchedules = GroupSchedule::findAll();
			if ($GroupSchedules) {
				/* @var $GroupSchedules GroupSchedule[] */
				foreach($GroupSchedules as $GroupSchedule) {
					$VisitJournal = VisitJournal::find([
												'condition' => "id_group={$GroupSchedule->id_group} ".
															   "AND lesson_date='{$GroupSchedule->date}' ".
															   "AND lesson_time='{$GroupSchedule->time}' "
									]);
					if ($VisitJournal) {
						if ($GroupSchedule->id_branch != $VisitJournal->id_branch || $GroupSchedule->cabinet != $VisitJournal->cabinet) {
							$discrepancy[] = [$GroupSchedule, $VisitJournal];
						} else {
							$checkCnt++;
						}
					}
				}

				if (empty($discrepancy)) {
					echo 'Все записи журнала и расписания соответствуют по параметру филиал/кабинет';
				} else {
					$f = fopen('files/discrepancy.txt', 'w+');
					fwrite($f, "Количество несоответствий ".count($discrepancy)."\n");
					foreach ($discrepancy as $elem) {
						fwrite($f, "Занятие {$elem[0]->date} {$elem[0]->time} в группе № {$elem[0]->id_group} (не соответстует кабинет.)\n");
					}
				}
			} else {
				echo 'No visits';
			}
		}


		/**
         * Updating old group schedule records.
         * Sets group id for records.
         */
        public function actionTransferSchedule()
        {
            $Groups = Group::findAll();
            if ($Groups) {
                /* @var $Groups Group[] */
                foreach($Groups as $Group) {
                    $GroupSchedules = GroupSchedule::findAll(['condition' => 'id_group='.$Group->id]);
                    if ($GroupSchedules) {
                        /* @var $GroupSchedules GroupSchedule[] */
                        foreach ($GroupSchedules as $GroupSchedule) {
                        	$data = [];
                        	if ($Group->id_branch) {
                        		$data['id_branch'] = $Group->id_branch;
//                        		if ($Group->cabinet) {
//                        			$data['cabinet'] = $Group->cabinet;
//                        		}
                        	}
                        	if (!empty($data)) {
								$GroupSchedule->update($data);
							}
                        }
                    }
                }
            } else {
                echo 'No group schedule updated';
            }
        }
 
        public function actionTeacherLikes()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				$VisitJournal = Student::getExistedTeachers($Student->id);
				foreach ($VisitJournal as $VJ) {
					$Like = GroupTeacherLike::find([
						'condition' => "id_status > 0 AND id_student={$VJ->id_entity} AND id_teacher={$VJ->id_teacher}"
					]);

					if ($Like) {
						switch($Like->id_status) {
							case 1: {
								$new_rating = 5;
								break;
							}
							case 2: {
								$new_rating = 4;
								break;
							}
							case 3: {
								$new_rating = 3;
								break;
							}
						}

						$TeacherReview = TeacherReview::find([
							'condition' => "id_teacher={$VJ->id_teacher} AND id_student={$VJ->id_entity} AND id_subject={$VJ->id_subject}"
						]);

						if ($TeacherReview) {
							$TeacherReview->admin_rating = $new_rating;
							$TeacherReview->save('admin_rating');
						} else {
							TeacherReview::add([
								'id_student' => $Like->id_student,
								'id_teacher' => $Like->id_teacher,
								'id_subject' => $VJ->id_subject,
								'admin_rating' => $new_rating,
							]);
						}
					}
				}
			}








			// $TeacherLikes = GroupTeacherLike::findAll([
			// 	'condition' => 'id_status > 0'
			// ]);
			//
			// foreach ($TeacherLikes as $TeacherLike) {
			// 	switch($TeacherLike->id_status) {
			// 		case 1: {
			// 			$new_rating = 5;
			// 			break;
			// 		}
			// 		case 2: {
			// 			$new_rating = 4;
			// 			break;
			// 		}
			// 		case 3: {
			// 			$new_rating = 3;
			// 			break;
			// 		}
			// 	}
			//
			// 	$VisitJournal = Student::getExistedTeachers($TeacherLike->id_student);
			//
			//
			// 	foreach ($VisitJournal as $VJ) {
			// 		$TeacherReview = TeacherReview::find([
			// 			'condition' => "id_teacher={$VJ->id_teacher} AND id_student={$VJ->id_entity} AND id_subject={$VJ->id_subject}"
			// 		]);
			//
			// 		if ($TeacherReview) {
			// 			$TeacherReview->admin_rating = $new_rating;
			// 			$TeacherReview->save('admin_rating');
			// 		} else {
			// 			TeacherReview::add([
			// 				'id_student' => $TeacherLike->id_student,
			// 				'id_teacher' => $TeacherLike->id_teacher,
			// 				'id_subject' => $VJ->id_subject,
			// 				'admin_rating' => $new_rating,
			// 			]);
			// 		}
			// 	}
			// }
		}

		public function actionMango()
		{
			Mango::call();
		}

		public function actionTestyTest()
		{
			$Students = Student::getWithoutGroupErrors();

			h1(count($Students));

			preType($Students);
		}

		public function actionOnlyTeacherSms()
		{
			$teacher_ids = Group::getTeacherIds();

			$Teachers = Teacher::findAll([
				"condition" => "id IN (" . implode(',', $teacher_ids) . ")"
			]);

			$message = "Уважаемые преподаватели, пожалуйста, не используйте мобильные телефоны на занятиях. Администрация ЕГЭ-Центра по просьбам учеников.";

			foreach ($Teachers as $Teacher) {
				foreach (Student::$_phone_fields as $phone_field) {
					$phone_number = $Teacher->{$phone_field};
					if (!empty($phone_number)) {
						$messages[] = [
							"type"      => "Учителю #" . $Teacher->id,
							"number" 	=> $phone_number,
							"message"	=> $message,
						];
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

			Email::send("makcyxa-k@yandex.ru", "СМС", $body);
		}

		public function actionTestingSms()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				if ($Student->grade == 11) {
					$message = "ЕГЭ-Центр информирует: в ЕГЭ-Центре-Тургеневская можно пройти пробное тестирование ЕГЭ на официальных бланках. Записаться можно из личного кабинета (логин: {$Student->login}, пароль: {$Student->password}) либо по телефону (495) 646-85-92. Администрация.";

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> $message,
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> $message,
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

			Email::send("makcyxa-k@yandex.ru", "СМС о тестировании", $body);
		}

		public function actionUpdateSearchData()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				$text = "";
				$Requests = $Student->getRequests();
				foreach ($Requests as $Request) {
					$text .= $Request->name;
					$text .= self::_getPhoneNumbers($Request);
				}
				// Имя, телефоны ученика и представителя
				$text .= $Student->name();
				$text .= self::_getPhoneNumbers($Student);
				$text .= $Student->email;

				if ($Student->Passport) {
					$text .= $Student->Passport->series;
					$text .= $Student->Passport->number;
				}

				if ($Student->Representative) {
					$text .= $Student->Representative->name();
					$text .= self::_getPhoneNumbers($Student->Representative);
					$text .= $Student->Representative->email;
					$text .= $Student->Representative->address;

					if ($Student->Representative->Passport) {
						$text .= $Student->Representative->Passport->series;
						$text .= $Student->Representative->Passport->number;
						$text .= $Student->Representative->Passport->issued_by;
						$text .= $Student->Representative->Passport->address;
					}
				}

				// Последние 4 цифры номер карты
				$Payments = Payment::findAll([
					"condition" => "id_status=" . Payment::PAID_CARD . " AND entity_id=" . $Student->id . " and entity_type='".Student::USER_TYPE."' AND card_number!=''"
				]);
				foreach ($Payments as $Payment) {
					$text .= $Payment->card_number;
				}

				$return[$Student->id] = $text;
			}

			dbConnection()->query("TRUNCATE TABLE search_students");

			foreach ($return as $id_student => $text) {
				$values[] = "($id_student, '" . $text . "')";
			}

			dbConnection()->query("INSERT INTO search_students (id_student, search_text) VALUES " . implode(",", $values));
		}

		private static function _getPhoneNumbers($Object)
		{
			$text = "";
			foreach (Student::$_phone_fields as $phone_field) {
				$phone = $Object->{$phone_field};
				if (!empty($phone)) {
					$text .= $phone;
				}
			}
			return $text;
		}

		public function actionStudentsWithoutGrade()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				if (!$Student->grade) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}

		public function actionCalculateRemainder()
		{
			$Students = Student::getWithContract(true);

			$student_ids = [];
			foreach ($Students as $Student) {
				$Contract = $Student->getContracts()[0];
				$Payments = $Student->getPayments();

				// сумма последней версии текущего договора минус сумма платежей и плюс сумма возвратов
				$remainder = $Contract->sum;

				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						$remainder -= $Payment->sum;
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
						$remainder += $Payment->sum;
					}
				}

				PaymentRemainder::add([
					"id_student"	=> $Student->id,
					"remainder"		=> $remainder,
				]);
			}
		}

		public function actionEgecentr()
		{
			$this->addJs("ng-test-app");

			$date_start = "2013-09-01";
			$date_end = "2014-05-31";


			do {
				$dates[] = $date_start;
				$date_start = date("Y-m-d", strtotime("$date_start + 1 day"));
			} while ($date_start <= $date_end);

			$ang_init_data = angInit([
				"dates" => $dates,
			]);

			$this->setTabTitle("test");
			$this->render("egecentr", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionAgash()
		{
			$id_branch 	= 1;
			$subjects	= [1, 2];
			$grade 		= 10;

			$subjects_ids = implode(",", $subjects);

			foreach(range(0, 7) as $day) {
				$count = 0;
				$date = date("Y-m", strtotime("-$day months"));

				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') >= '$date-01'
						AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date-31'
						AND cancelled=0 " . Contract::ZERO_OR_NULL_CONDITION
				]);

				foreach ($Contracts as $Contract) {
					$ContractSubjects = ContractSubject::findAll([
						"condition" => "id_contract=" . $Contract->id . ($id_subject ? " AND id_subject IN ($subjects_ids)" : "")
					]);

					if ($ContractSubjects) {
						foreach ($ContractSubjects as $Subject) {
							// Находим группу по параметрам
							$Group = Group::count([
								"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Contract->id_student},%'
									AND id_subject = {$Subject->id_subject}
									AND grade = {$grade}
									AND id_branch={$id_branch}"
							]);

							if ($Group) {
								$count++;
							}
						}
					}
				}


				$return[] = [
					"month" => date("F", strtotime("-$day months")),
					"count"	=> $count,
				];
			}

			$return = array_reverse($return);

			preType($return);
		}

		public function actionSwitchTest()
		{
			$this->addCss("bs-slider");
			$this->addJs("bs-slider");
			$this->setTabTitle("test");
			$this->render("test");
		}

		public function actionSLessons()
		{
			$Student = Student::findById(288);

			$Data = $Student->getVisits();

			preType($Data);
		}

		public function actionPhpExcel()
		{
/*
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="расписание.xls"');
			header('Cache-Control: max-age=0');
*/

			$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0);

			$objPHPExcel->getActiveSheet()->SetCellValue('B3', 'ПОНЕДЕЛЬНИК');
			$objPHPExcel->getActiveSheet()->mergeCells('B3:C3');

			$objPHPExcel->getActiveSheet()->SetCellValue('B4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('C4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('D3', 'ВТОРНИК');
			$objPHPExcel->getActiveSheet()->mergeCells('D3:E3');

			$objPHPExcel->getActiveSheet()->SetCellValue('D4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('E4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('F3', 'СРЕДА');
			$objPHPExcel->getActiveSheet()->mergeCells('F3:G3');

			$objPHPExcel->getActiveSheet()->SetCellValue('F4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('G4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('H3', 'ЧЕТВЕРГ');
			$objPHPExcel->getActiveSheet()->mergeCells('H3:I3');

			$objPHPExcel->getActiveSheet()->SetCellValue('H4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('I4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('J3', 'ПЯТНИЦА');
			$objPHPExcel->getActiveSheet()->mergeCells('J3:K3');

			$objPHPExcel->getActiveSheet()->SetCellValue('J4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('K4', '18:40');


			$Cabinets = Cabinet::findAll([
				"condition" => "id_branch=" . Branches::TRG,
			]);


			$row = 5;
			$col = 'A';

			foreach ($Cabinets as $Cabinet) {
				$objPHPExcel->getActiveSheet()->SetCellValue($col.$row, 'Кабинет ' . $Cabinet->number);
				$row++;

				// Cabinet groups
				$Groups = Group::findAll([
					"condition" => "cabinet=" . $Cabinet->id
				]);

				preType($Groups, 1);
			}

			exit();
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save('php://output');
		}

		public function actionReviewCount()
		{
			$Groups = Group::findAll();

			foreach ($Groups as $Group) {
				foreach ($Group->students as $id_student) {
					$Student = Student::findById($id_student);
					$Teacher = Teacher::findById($Group->id_teacher);

					$Student->already_had_lesson	= $Student->alreadyHadLesson($Group->id);

					$Student->review_status	= $Group->student_statuses[$Student->id]['review_status'];

					if ($Student->already_had_lesson) {
						$total_count++;
						if (!$Student->review_status) {
							$gray_count++;
							$data[] = [
								'sort'		=> 0,
								'class' 	=> 'not-collected',
								'Teacher'	=> $Teacher,
								'Student'	=> $Student,
								'id_group'	=> $Group->id,
							];
							//echo "GROUP ID: {$Group->id} | STUDENT ID: {$Student->id} <br>";
						} else {
							switch ($Student->review_status) {
								case 1: {
									$green_count++;
									$data[] = [
										'sort'		=> 1,
										'class' 	=> 'collected',
										'Teacher'	=> $Teacher,
										'Student'	=> $Student,
										'id_group'	=> $Group->id,
									];
									break;
								}
								case 2: {
									$orange_count++;
									$data[] = [
										'sort'		=> 2,
										'class' 	=> 'orange',
										'Teacher'	=> $Teacher,
										'Student'	=> $Student,
										'id_group'	=> $Group->id,
									];
									break;
								}
								case 3: {
									$red_count++;
									$data[] = [
										'sort'		=> 3,
										'class' 	=> 'red',
										'Teacher'	=> $Teacher,
										'Student'	=> $Student,
										'id_group'	=> $Group->id,
									];
									break;
								}
							}
						}
					}
				}
			}

			usort($data, function($a, $b) {
				return $a['sort'] - $b['sort'];
			});

			$this->setTabTitle("Количество отзывов");

			$this->render("review_count", [
				"data" => $data,
				"gray_count" => $gray_count,
				"green_count" => $green_count,
				"orange_count" => $orange_count,
				"red_count"	=> $red_count,
				"total_count" => $total_count,
			]);

// 			echo "GRAY: $gray_count | GREEN: $green_count | ORANGE: $orange_count | RED: $red_count <br> TOTAL: $total_count";
		}


		/**
		 * у которых договор есть, но нет ни одного посещения ни в одной группе.
		 *
		 * @access public
		 * @return void
		 */
		public function actionWithContractButNoLessons()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$result = dbConnection()->query("
					SELECT COUNT(*) as cnt FROM visit_journal
					WHERE id_entity={$Student->id} AND type_entity='STUDENT'
				");

				if ($result->fetch_object()->cnt == 0) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}

		/**
		 * у которых есть хоть одна группа, в которой ученики прекратили занятия.
		 *
		 */
		public function actionStoppedGroup()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$group_ids = Group::getIds([
					"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%'"
				]);

				$result = dbConnection()->query("
					SELECT id_group FROM visit_journal
					WHERE id_entity={$Student->id} AND type_entity='STUDENT'
					GROUP BY id_group
				");

				$group_ids2 = [];
				while ($row = $result->fetch_object()) {
					$group_ids2[] = $row->id_group;
				}

				foreach ($group_ids2 as $id_group) {
					if (!in_array($id_group, $group_ids)) {
						$student_ids[] = $Student->id;
						break;
					}
				}

/*
				$diff = array_diff($group_ids, $group_ids2);

				if (count($diff) > 0) {
					h1($Student->id);
					preType([
						$group_ids, $group_ids2
					]);
					$student_ids[] = $Student->id;
				}
*/
			}

			echo implode(", ", $student_ids);
		}

		public function actionHasGreenOrYellowSubjectInOriginal()
		{
			$result = dbConnection()->query("
				SELECT c.id_student FROM contract_subjects cs
				LEFT JOIN contracts c ON c.id = cs.id_contract
				WHERE cs.status IN (1, 2)
				GROUP BY c.id_student
			");

			while ($row = $result->fetch_object()) {
				$student_ids[] = $row->id_student;
			}

			echo implode(", ", $student_ids);
		}

		public function actionHasGreenOrYellowSubjectInVersion()
		{
			$result = dbConnection()->query("
				SELECT c.id_contract FROM contract_subjects cs
				LEFT JOIN contracts c ON c.id = cs.id_contract
				WHERE cs.status IN (1, 2) AND (c.id_student IS NULL or c.id_student=0) AND c.id_contract > 0
				GROUP BY c.id_contract
			");

			while ($row = $result->fetch_object()) {
				$contract_ids[] = $row->id_contract;
			}
			$contract_ids_string = implode(", ", $contract_ids);

			$result = dbConnection()->query("
				SELECT id_student FROM contracts WHERE id IN ({$contract_ids_string})
			");

			while ($row = $result->fetch_object()) {
				$student_ids[] = $row->id_student;
			}


			echo implode(", ", $student_ids);
		}

		public function actionTwoOrMoreVersions()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$Student->Contract = $Student->getLastContract();

				if (!$Student->Contract->isOriginal()) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}

		public function actionTwoOrMoreContracts()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$count = Contract::count([
					"condition" => "id_student={$Student->id} ".Contract::ZERO_OR_NULL_CONDITION
				]);

				if ($count > 1) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}
		
		public function actionPlusYear()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				// кол-во цепей
				$count = Contract::count([
					"condition" => "id_student={$Student->id} ".Contract::ZERO_OR_NULL_CONDITION
				]);
				
				// если одна цепь
				if ($count == 1) {
					// находим договор 2015-2016 учебного года
					$Contract = Contract::find([
						"condition" => "id_student={$Student->id} AND year=2015 ".Contract::ZERO_OR_NULL_CONDITION
					]);
					// есил договор нашелся
					if ($Contract && ($Student->grade < 12)) {
						$Student->grade++;
						$Student->save('grade');
					}
				}
			}
			// echo implode(", ", $student_ids);
		}

		public function actionSameNumber()
		{
			$Requests = Request::findAll([
				"condition" => "adding=0",
				"limit"		=> "100 OFFSET 100",
			]);

			$request_ids = [];
			foreach ($Requests as $Request) {
				foreach (Student::$_phone_fields as $phone_field) {
					$request_phone = $Request->{$phone_field};
					if (!empty($request_phone)) {
						if (isDuplicate($request_phone, $Request->id)) {
							$request_ids[] = $Request->id;
							break;
						}
					}

					$student_phone = $Request->Student->{$phone_field};
					if (!empty($student_phone)) {
						if (isDuplicate($student_phone, $Request->id)) {
							$request_ids[] = $Request->id;
							break;
						}
					}

					if ($Request->Student->Representative) {
						$representative_phone = $Request->Student->Representative->{$phone_field};
						if (!empty($representative_phone)) {
							if (isDuplicate($representative_phone, $Request->id)) {
								$request_ids[] = $Request->id;
								break;
							}
						}
					}
				}
			}

			preType($request_ids);
		}

		public function actionGroupContractCacnelled()
		{
			$Students = Student::getWithContract();
			foreach ($Students as $Student) {
				$Student->Contract = $Student->getLastContract();
				$subject_ids = [];
				foreach ($Student->Contract->subjects as $subject) {
					$subject_ids[] = $subject['id_subject'];
				}
				// preType($Student->Contract->subjects);

				if (count($subject_ids)) {
					$count = Group::count([
						"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject NOT IN (" . implode(",", $subject_ids) . ")"
					]);
					if ($count > 0) {
						h1($Student->id);
					}
				}
			}
		}

		public function actionSameDay()
		{
			$Students = Student::getWithContract();
			foreach ($Students as $Student) {
				$Groups = $Student->getGroups();
				foreach ($Groups as $Group) {
					foreach ($Group->day_and_time as $day => $time_data) {
						foreach ($time_data as $time) {
							$result = dbConnection()->query("
								SELECT COUNT(*) AS cnt FROM groups g
									LEFT JOIN group_time gt ON gt.id_group = g.id
									WHERE CONCAT(',', CONCAT(g.students, ',')) LIKE '%,{$Student->id},%' AND gt.day = {$day} AND gt.time = '{$time}'
							");
							$count = $result->fetch_object()->cnt;
							if ($count > 1) {
								h1($Student->id);
							}
						}
					}
				}
			}
		}
/*

		public function actionOneSubject()
		{
			$Students = Student::getWithContract();


			foreach ($Students as $Student) {
				$Groups = $Student->getGroups();

				foreach ($Groups as $Group) {
					$count = Group::count([
						"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject={$Group->id_subject}"
					]);

					if ($count > 1) {
						h1($Student->id);
					}
				}
		}

*/
		public function actionMatch()
		{
			$Group = Group::findById(73);

			var_dump($Group->lessonDaysMatch());
		}


		public function actionSmsCheckLate()
		{
			$result = dbConnection()->query("select * from sms where message LIKE '%опоздал%' or  message LIKE '%отсутствовал%'");

			while ($row = $result->fetch_object())
			{
				$all_sms[] = $row;
			}


			foreach ($all_sms as &$sms) {
				$phone = $sms->number;

				$sms->message = preg_replace('!\s+!', ' ', $sms->message);
				preg_match("/информирует: ([\d]+) ([\w]+) ([\w-]+[\s]*[\w-]+) ([\w]+)/u", $sms->message, $matches);

				$presence = false;

				if (strpos($matches[4], "отсутствовал") !== false) {
					$presence = 2;
				} else
				if (strpos($matches[4], "опоздал") !== false) {
					$presence = 1;
				}

				if ($presence) {
					$month = russian_month_id_by_name($matches[2]);
					if ($month < 10) {
						$month = "0" . $month;
					}
					$date = "2015-{$month}-{$matches[1]}";

					list($last_name, $first_name) = explode(" ", $matches[3]);

					$result = dbConnection()->query("
						SELECT * FROM visit_journal vj
						LEFT JOIN students s on s.id = vj.id_entity
						WHERE vj.type_entity = 'STUDENT' AND s.first_name = '{$first_name}'
							AND s.last_name = '{$last_name}' AND vj.lesson_date = '$date' AND ". ($presence == 2 ? "vj.presence=2" : "(vj.presence=1 AND vj.late > 0)") ."
					");

					$count_all++;

					if ($result->num_rows) {
						$count_correct++;
					}

// 					h1($result->num_rows);
				}

// 				h1($matches[4] . "-" . $presence);
// 				preType($matches);
			}
			echo "ALL: $count_all | CORRECT: $count_correct";
// 			preType($all_sms);

/*
			$this->setTabTitle("Проверка СМС");

			$this->render("sms_check", [
				"all_sms" => $all_sms
			]);
*/
		}



		public function actionSmsCheck()
		{
			$result = dbConnection()->query("select * from sms where message LIKE '%ожидается%'");

			while ($row = $result->fetch_object())
			{
				$all_sms[] = $row;
			}


			foreach ($all_sms as &$sms) {
				$phone = $sms->number;

				$sms->message = preg_replace('!\s+!', ' ', $sms->message);
				preg_match("/ченик ([\w-]+[\s]*[\w-]+)[\s]*ожидается на первое занятие по ([\w]+[\s]?[\w]*) в ЕГЭ-Центр-([\w]+) ([\d]+) ([\w]+)[\s]*[в]?[\s]*([\d:]+)?. Кабинет ([\d]+)./u", $sms->message, $matches);

				$id_subject = array_search($matches[2], Subjects::$dative);
				$id_branch 	= array_search($matches[3], Branches::$all);

				$Student = dbConnection()->query("
					select s.id, s.first_name, s.last_name from students s
					left join representatives r on s.id_representative = r.id
					where (s.phone = '$phone' OR s.phone2 = '$phone' OR s.phone3 = '$phone')
					or (r.phone = '$phone' OR r.phone2 = '$phone' OR r.phone3 = '$phone')
					LIMIT 1
				")->fetch_object();

				$id_student = $Student->id;

				// если студент не найден
				if (!$id_student) {
					$sms->status 		= 0;
					$sms->status_text 	= "УЧЕНИКА С ТАКИМ НОМЕРОМ НЕ НАЙДЕНО";
					continue;
				}

				if ($id_subject && $id_branch && $id_student) {
					$Group = Group::find([
						"condition" => "id_branch=$id_branch AND id_subject=$id_subject AND CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%'"
					]);
					if ($Group) {
						// проверка даты и времени первого занятия
						$Group->first_schedule = $Group->getFirstSchedule(false);

						// проверка имени и фамилии студента
						$name = explode(" ", $matches[1]);
						if (strcmp(trim($name[0]), trim($Student->last_name)) !== 0 || strcmp(trim($name[1]), trim($Student->first_name))) {
							$sms->status 		= 0;
							$sms->status_text 	= "ИМЯ НЕ СОВПАДАЕТ ({$Student->last_name} {$Student->first_name} | {$name[0]} {$name[1]})";
							continue;
						}

						// проверка статуса согласия студента
						if (!$Group) {
							$sms->status 		= 0;
							$sms->status_text 	= "ГРУППА НЕ НАЙДЕНА ($id_branch | $id_subject | $id_student)";
							continue;
						}


						$Status = GroupStudentStatuses::find([
							"condition" => "id_student=$id_student AND id_group={$Group->id}"
						]);

						if ($Status->id_status != GroupStudentStatuses::AGREED) {
							$sms->not_agreed = true;
						}

						if ($Status->notified != 1) {
//							echo $Group->id . " | " . $Student->id . "<br>";
//							$Status->notified = 1;
//							$Status->save("notified");
							$sms->not_notified = true;
						}

						$date_day = date("j", strtotime($Group->first_schedule->date));

						if ($date_day != $matches[4]) {
							$sms->status 		= 0;
							$sms->status_text 	= "НЕПРАВИЛЬНАЯ ДАТА ($date_day | {$matches[4]})";
							continue;
						}

						if (mb_strimwidth($Group->first_schedule->time, 0, 5) != $matches[6]) {
							$sms->status 		= 0;
							$sms->status_text 	= "НЕПРАВИЛЬНОЕ ВРЕМЯ (" . mb_strimwidth($Group->first_schedule->time, 0, 5) . " | {$matches[6]})";
							continue;
						}

						$cabinet_number = Cabinet::findById($Group->cabinet)->number;

						if ($cabinet_number != $matches[7]) {
							$sms->status 		= 0;
							$sms->status_text 	= "КАБИНЕТЫ НЕ СОВПАДАЮТ";
							continue;
						}

						$sms->status 		= 1;
						$sms->status_text 	= "ОК";
						continue;

					} else {
						$sms->status 		= 0;
						$sms->status_text 	= "ГРУППА НЕ НАЙДЕНА ($id_branch | $id_subject | $id_student)";
					}
				} else {
					$sms->status 		= 0;
					$sms->status_text 	= "НЕ ПОДХОДИТ ПОД РЕГУЛЯРНОЕ ВЫРАЖЕНИЕ";
				}
			}

//			preType($all_sms);

			$this->setTabTitle("Проверка СМС");

			$this->render("sms_check", [
				"all_sms" => $all_sms
			]);
		}

		public function actionGo()
		{
			$Groups = Group::findAll([
				"condition" => "id_branch=" . Branches::PVN,
			]);

// 			$add_students = [1851, 2111, 1910, 2051];

			foreach ($Groups as $Group) {
				if ($Group->id_teacher) {
					$Teacher = Teacher::findById($Group->id_teacher);
					foreach (Student::$_phone_fields as $phone_field) {
						$teacher_number = $Teacher->{$phone_field};
						if (!empty($teacher_number)) {
							$messages[] = [
								"type"      => "Учителю #" . $Teacher->id,
								"number" 	=> $teacher_number,
								"message"	=> self::_generateMessage($Teacher),
							];
						}
					}
				}
				foreach ($Group->students as $id_student) {
					if (!in_array($id_student, $add_students)) {
						continue;
					}
					$Student = Student::findById($id_student);

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> self::_generateMessage($Student),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateMessage($Student),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			$final = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
// 					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];
					$final[] = $message;
					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

//			Email::send("makcyxa-k@yandex.ru", "Уведомление о личном кабинете, Калужская", $body);
			preType($final);
		}

		private function _generateMessage($Entity)
		{
			return "ЕГЭ-Центр информирует: доступ в личный кабинет (на сайте ЕГЭ-Центра ссылка вверху справа) логин – {$Entity->login}, пароль {$Entity->password}";
		}

		public function actionSetTeacherLogin()
		{
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			$Teachers = Teacher::findAll();

			foreach ($Teachers as $Teacher) {
				$Teacher->login 	= $Teacher->_generateLogin();
				$Teacher->password	= $Teacher->_generatePassword();

				User::add([
					"login" 		=> $Teacher->login,
					"password"		=> $Teacher->password,
					"first_name"	=> $Teacher->first_name,
					"last_name"		=> $Teacher->last_name,
					"middle_name"	=> $Teacher->middle_name,
					"type"			=> Teacher::USER_TYPE,
					"id_entity"		=> $Teacher->id
				]);

				$Teacher->save();

				preType($Teacher);
			}
		}

		public function actionSetStudentLogin()
		{
			$Students = Student::getWithContract();
/*
			$Students = Student::findAll([
				"condition" => "id IN (1851, 2111, 1910, 2051)"
			]);
*/

			foreach ($Students as $Student) {
				if (!$Student->login) {
					echo $Student->id . "<br>";
				}
/*
				$Student->Contract 	= $Student->getLastContract();
				$Student->login 	= $Student->Contract->id;
				$Student->password	= mt_rand(10000000, 99999999);
				$Student->save();

				User::add([
					"login" 	=> $Student->login,
					"password"	=> $Student->password,
					"first_name"	=> $Student->first_name,
					"last_name"		=> $Student->last_name,
					"middle_name"	=> $Student->middle_name,
					"type"			=> Student::USER_TYPE,
					"id_entity"		=> $Student->id
				]);
*/
			}
		}

		public function actionSetStudentCode()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				// if (!$Student->code) {
					$Student->code = Contract::_generateCode();
					$Student->save("code");
				//}
			}
		}

/*
		public function action()
		{
			$Students = Student::getWithContract(true);

			$nc = new NCLNameCaseRu();

			$messages = [];
			foreach ($Students as $Student) {
				$student_gender = $nc->genderDetect($Student->last_name . " "
							. $Student->first_name . " " . $Student->middle_name);
				foreach (Student::$_phone_fields as $phone_field) {
					$student_number = $Student->{$phone_field};
					if (!empty($student_number)) {
						$messages[] = [
							"number" 	=> $student_number,
							"message"	=> ($student_gender == 1 ? "Уважаемый" : "Уважаемая") . " {$Student->first_name} {$Student->middle_name}, ЕГЭ-Центр активно формирует группы и расписание. Ежегодно, как и в этом году, занятия начинаются с 15 по 30 сентября. Перед началом занятий мы обязательно с Вами свяжемся. Спасибо за понимание. Администрация ЕГЭ-Центра."
						];
					}

					if ($Student->Representative) {
						$representative_gender = $nc->genderDetect($Student->Representative->last_name . " "
							. $Student->Representative->first_name . " " . $Student->Representative->middle_name);
						$representative_number = $Student->Representative->{$phone_field};
						if (!empty($representative_number)) {
							$messages[] = [
								"number" 	=> $representative_number,
								"message"	=> ($representative_gender == 1 ? "Уважаемый" : "Уважаемая") . " {$Student->Representative->first_name} {$Student->Representative->middle_name}, ЕГЭ-Центр активно формирует группы и расписание. Ежегодно, как и в этом году, занятия начинаются с 15 по 30 сентября. Перед началом занятий мы обязательно с Вами свяжемся. Спасибо за понимание. Администрация ЕГЭ-Центра."
							];
						}
					}
				}
			}

			$sent_to = ['79670270752', '79031231801', '79037457698', '79251285692', '79163301472', '79164306272', '79654492601', '79096451438', '79175883100', '79153460947', '74953142024', '79055555825', '79099526366', '79853550349', '79037755318', '79852699043', '79653994501', '79152446686', '79857808032', '79030155035', '79169291117', '79257318384', '79175630479', '79166259015', '79166976092', '79166059905', '79169901330', '79652064827', '79032828225', '79152191898', '79104049172', '79152690638', '79165339308', '79852792608', '79055907327', '79060568266', '79166705602', '79150075824', '79164521239', '79859780281', '79152381922', '79680619395', '79629271004', '79165480965', '79161710291', '79152330527', '79150072764', '79636724119', '79057799915', '79859659477', '79162310335', '79160305385', '79166161406', '79035071873', '79166390009', '79175605578', '79030101149', '79037805451', '79151887521', '79166878687', '79998262848', '79263414810', '79169558280', '79168509009', '79168153574', '79175658506', '79197789113', '79653132515', '79032874994', '79032767814', '79167437499', '79163102484', '79175776664', '79163947757', '79637896620', '79165035329', '79096478884', '79629238497', '79060663807', '79055039481', '79067895132', '79037842820', '79168793353', '79859228141', '79269127329', '79684492848', '79060535047', '79036157176', '79037335976', '79264542105', '79853899036', '79153272754', '79169536667', '79683364322', '79859952254', '79164741454', '79163533037', '79168790332', '79160703690', '79037533242', '79037413040', '79672915048', '79035242404', '79647834373', '79036119530', '79251908463', '79636488591', '79639245523', '79267080163', '79268279111', '79299623626', '79295184080', '79262489217', '79264291913', '79031778317', '79685148381', '79168238741', '79853047100', '79160446718', '79164911512', '79154670326', '79168086738', '79689657517', '79851988946', '79175548554', '79629381537', '79031218805', '79672582855', '79161572919', '79096595455', '79636058802', '79250298355', '79263374369', '79035716447', '79265381197', '79035716467', '79036819268', '79647993371', '79194100124', '79265728638', '79036710491', '79036710489', '79162612220', '79166661129', '79153866202', '79163349589', '79160412379', '79175404446', '79150367931', '79104281257', '79851408180', '79165050858', '79250236096', '79262720500', '79055347193', '79037779277', '79150774972', '79153978881', '79636661438', '79166233805', '79652195014', '79647058120', '79685286554', '79037759411', '79266989106', '79261936833', '79851786672', '79168468245', '79168334676', '79161354015', '79099566952', '79035400133', '79253709094', '79165054682', '79261542040', '79266965344', '79150190567', '79199675935', '79854580882', '79104880192', '79670763053', '79096831152', '79035260414', '79167469551', '79260140057', '79295798008', '79035158899', '79037757657', '79165366325', '79036175095', '79854310778', '79162005025', '79091569166', '79036856688', '79150318065', '79166202089', '79036816083', '79636444830'];

			$sent_to_new = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
// 					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];
					$sent_to_new[] = $message['number'];
				}
			}

			preType($sent_to_new);
			echo "<hr>";
			echo count($sent_to_new);
		}
*/

		public function actionCabinetsCheck()
		{
			$r = Cabinet::getCabinetGroups(1);

			preType($r);
		}

		public function actionSetCabinet()
		{
			$Groups = Group::findAll();

			foreach ($Groups as $Group) {
				$Cabinets = Cabinet::findAll([
					"condition" => "id_branch=" . $Group->id_branch
				]);

				if ($Cabinets) {
					$Group->cabinet = $Cabinets[0]->id;
					$Group->save("cabinet");
				}
			}
		}

		public function actionStudentFreetime()
		{
			$Student = Student::findById(473);
			$ft = $Student->getGroupFreetime(208);
			preType($ft);
		}

		public function actionTeacherFreetime()
		{
			$Teachers = Teacher::findAll();

			foreach ($Teachers as $Teacher) {
				foreach ($Teacher->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}

					dbConnection()->query("
						INSERT INTO teacher_freetime
							(id_teacher, id_branch, day, time)
						VALUES
							({$Teacher->id}, $id_branch, 1, '16:15'),
							({$Teacher->id}, $id_branch, 1, '18:40'),

							({$Teacher->id}, $id_branch, 2, '16:15'),
							({$Teacher->id}, $id_branch, 2, '18:40'),

							({$Teacher->id}, $id_branch, 3, '16:15'),
							({$Teacher->id}, $id_branch, 3, '18:40'),

							({$Teacher->id}, $id_branch, 4, '16:15'),
							({$Teacher->id}, $id_branch, 4, '18:40'),

							({$Teacher->id}, $id_branch, 5, '16:15'),
							({$Teacher->id}, $id_branch, 5, '18:40'),

							({$Teacher->id}, $id_branch, 6, '11:00'),
							({$Teacher->id}, $id_branch, 6, '13:30'),
							({$Teacher->id}, $id_branch, 6, '16:00'),
							({$Teacher->id}, $id_branch, 6, '18:30'),

							({$Teacher->id}, $id_branch, 7, '11:00'),
							({$Teacher->id}, $id_branch, 7, '13:30'),
							({$Teacher->id}, $id_branch, 7, '16:00'),
							({$Teacher->id}, $id_branch, 7, '18:30')
					");

					echo ("
						INSERT INTO teacher_freetime
							(id_teacher, id_branch, day, time)
						VALUES
							({$Teacher->id}, $id_branch, 1, '16:15'),
							({$Teacher->id}, $id_branch, 1, '18:40'),

							({$Teacher->id}, $id_branch, 2, '16:15'),
							({$Teacher->id}, $id_branch, 2, '18:40'),

							({$Teacher->id}, $id_branch, 3, '16:15'),
							({$Teacher->id}, $id_branch, 3, '18:40'),

							({$Teacher->id}, $id_branch, 4, '16:15'),
							({$Teacher->id}, $id_branch, 4, '18:40'),

							({$Teacher->id}, $id_branch, 5, '16:15'),
							({$Teacher->id}, $id_branch, 5, '18:40'),

							({$Teacher->id}, $id_branch, 6, '11:00'),
							({$Teacher->id}, $id_branch, 6, '13:30'),
							({$Teacher->id}, $id_branch, 6, '16:00'),
							({$Teacher->id}, $id_branch, 6, '18:30'),

							({$Teacher->id}, $id_branch, 7, '11:00'),
							({$Teacher->id}, $id_branch, 7, '13:30'),
							({$Teacher->id}, $id_branch, 7, '16:00'),
							({$Teacher->id}, $id_branch, 7, '18:30')
					")."<br><br>";

					echo dbConnection()->error . "<hr>";
				}
			}
		}

		/**
		 * @deprecated
		 */
		public function actionDeleteCache()
		{
			foreach (Branches::$all as $id_branch => $name) {
				memcached()->delete("Rating[$id_branch]");
				memcached()->delete("UniqueRating[$id_branch]");
				memcached()->delete("MaxRating[$id_branch]");
			}
//			memcached()->delete("Rating");
		//	memcached()->delete("SumRating");
		}


		public function actionTransferDayAndTime()
		{
			$Groups = Group::findAll([
				"condition" => "start!='' AND start IS NOT NULL"
			]);

			foreach ($Groups as $Group) {
				$GroupTime = new GroupTime([
					"id_group" 	=> $Group->id,
					"day"		=> $Group->day,
					"time"		=> $Group->start,
				]);

				$GroupTime->save();
			}
		}


		private static function addFreetime($Student, $day, $time) {
			foreach ($Student->branches as $id_branch) {
				if (!$id_branch) {
					continue;
				}
				$FreetimeNew = new FreetimeNew([
					"id_student"	=> $Student->id,
					"id_branch"		=> $id_branch,
					"day"			=> $day,
					"time"			=> $time,
				]);
				$FreetimeNew->save();
			}
		}

		public function actionTesty()
		{
			$Students = Student::getWithContract(true);
			h1(count($Students));


			// Добавляем догавары к студентам
			foreach ($Students as $index => $Student) {
// 				echo $index . ") " . $Student->last_name .  "<br>";

				$Students[$index]->Contract 	= $Student->getLastContract();
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Students[$index]->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
			}

// 			preType($Students);
//			preType($Students[326]);

			echo "<hr>";

			// Формируем по классам, всех студентов, кто не принадлежит группам
			foreach ($Students as $index => $Student) {
//				echo $index . ") " . $Student->last_name .  "<br>";
				$GroupsGrade[$Student->Contract->grade][] = $Student;
			}


			// Формируем по предметам
			foreach ($GroupsGrade as $grade => $GS)
			{
				foreach ($GS as $Student) {
					foreach ($Student->Contract->subjects as $subject) {
						foreach ($Student->branches as $id_branch) {
							if (!$id_branch) {
								continue;
							}
							$GroupStudents[$grade][$subject['id_subject']][$id_branch][] = $Student;
						}
					}
				}
			}

			// Формируем отдельные группы из массива (до примыкания к филиалу)
			foreach ($GroupStudents as $_grade => $_SubjectBranch) {
				foreach ($_SubjectBranch as $_subject => $_Branch) {
					foreach ($_Branch as $_branch => $BS) {
						foreach ($BS as $index => $S) {
							if ($S->inOtherGradeSubjectGroup($grade, $subject)) {
								unset($BS[$index]);
							}
						}
						// если есть ученики в группе
						if (count($BS)) {
							$GroupsFull[] = [
								"grade"		=> $_grade,
								"subject"	=> $_subject,
								"branch"	=> $_branch,
								"branch_svg"=> Branches::getName($_branch),
								"count"		=> count($BS),
								"Students"	=> $BS,
							];
						}
					}
				}
			}

			preType($GroupsFull);

/*

			// Сортируем по количеству учеников
			usort($GroupsFull, function($a, $b) {
				return ($a['count'] > $b['count'] ? -1 : 1);
			});

*/
// 			preType($GroupsFull);

			h1(count($Students));
		}

		public function actionDeleteUsersCache()
		{
			memcached()->delete("Users");
		}

		public function actionBranchesDelete()
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);


			$branch_str = Branches::STR;
			$branch_prr = Branches::PRR;

			$RequestsStr = Request::findAll([
				"condition" => "id_branch=$branch_str"
			]);

			$RequestsPrr = Request::findAll([
				"condition" => "id_branch=$branch_prr"
			]);

			$StudentsStr = Student::findAll([
				"condition" => "CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$branch_str},%'"
			]);

			$StudentsPrr = Student::findAll([
				"condition" => "CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$branch_prr},%'"
			]);

			preType($RequestsStr);

/*
			foreach ($RequestsStr as $Request) {
				$Request->id_branch = Branches::MLD;
				$Request->save("id_branch");
			}

			foreach ($RequestsPrr as $Request) {
				$Request->id_branch = Branches::VLD;
				$Request->save("id_branch");
			}
*/
		}

		public function actionMap()
		{
			$this->setTabTitle("Тестирование алгоритма метро");

			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller, ng-test-app");
			$this->render("map");
		}

		public function actionImap()
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);

			$mailbox = new PhpImap\Mailbox('{imap.yandex.ru:993/imap/ssl}', 'info@ege-centr.ru', 'kochubey1981');

//			$mailbox->statusMailbox();
//			$mailbox->testy();

//			$t = $mailbox->statusMailbox();

			$mailsIds = $mailbox->searchMailBox('ANSWERED');

			foreach ($mailsIds as $id_mail) {
				$mail = $mailbox->getMail($id_mail);
				preType($mail);
			}

//			var_dump($mailsIds);
//			preType($mailbox);
		}

		public function actionRating()
		{
			$Students = Student::findAll([
				"condition" => "branches != ''"
			]);

			foreach ($Students as &$Student) {
				$Student->Contract = $Student->getLastContract();
			}


			foreach ($Students as $Student) {
				foreach ($Student->branches as $id_branch) {
					$rating[$id_branch]++;
					if ($Student->Contract) {
						$rating[$id_branch] += count($Student->Contract->subjects);
					}
				}
			}

			asort($rating);
			$rating = array_reverse($rating, true);

			foreach ($rating as $id_branch => $score) {
				echo Branches::$all[$id_branch].": ".$score;
				echo "<br>";
			}
		}

		public function actionRatingCache()
		{
			$Rating = memcached()->get("Rating");

			preType($Rating);
		}

		// Перевести номера телефонов из форматированных
		public function actionUpdatePhones()
		{
			$Requests = Request::findAll([
				"condition" => "adding = 0 && (phone !='' OR phone2 != '' OR phone3 != '')",
			]);

			$Students = Student::findAll([
				"condition" => "phone !='' OR phone2 != '' OR phone3 != ''"
			]);

			$Representatives = Representative::findAll([
				"condition" => "phone !='' OR phone2 != '' OR phone3 != ''"
			]);

			foreach ($Requests as &$Request) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Request->{$phone_field} != "") {
						$Request->{$phone_field} = cleanNumber($Request->{$phone_field});
						$Request->save($phone_field);
					}
				}
			}

			foreach ($Students as &$Student) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Student->{$phone_field} != "") {
						$Student->{$phone_field} = cleanNumber($Student->{$phone_field});
						$Student->save($phone_field);
					}
				}
			}

			foreach ($Representatives as &$Representative) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Representative->{$phone_field} != "") {
						$Representative->{$phone_field} = cleanNumber($Representative->{$phone_field});
						$Representative->save($phone_field);
					}
				}
			}
		}

		##################################################
		###################### AJAX ######################
		##################################################


	}
