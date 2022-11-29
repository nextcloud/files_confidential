<?php

namespace OCA\Files_Confidential\Model;

use OCA\Files_Confidential\Contract\IStateClassification;

class StateClassification {
	public static function findLabelInText(string $text): int {
		foreach (self::LABELS as $country) {
			foreach ($country['labels'][0] as $label) {
				if (stripos($text, $label) !== false) {
					return IStateClassification::TOP_SECRET;
				}
			}
		}
		foreach (self::LABELS as $country) {
			foreach ($country['labels'][1] as $label) {
				if (stripos($text, $label) !== false) {
					return IStateClassification::SECRET;
				}
			}
		}
		foreach (self::LABELS as $country) {
			foreach ($country['labels'][2] as $label) {
				if (stripos($text, $label) !== false) {
					return IStateClassification::CONFIDENTIAL;
				}
			}
		}
		foreach (self::LABELS as $country) {
			foreach ($country['labels'][3] as $label) {
				if (stripos($text, $label) !== false) {
					return IStateClassification::RESTRICTED;
				}
			}
		}
		return 0;
	}

	// Source: https://en.wikipedia.org/wiki/Classified_information
	public const LABELS = [
		['country' => 'Albania', 'labels' => [['Teper Sekret'],['Sekret'],['Konfidencial'],['I Kufizuar']]],
		['country' => 'Argentina', 'labels' => [['Estrictamente Secreto y Confidencial'],['Secreto'],['Confidencial'],['Reservado']]],
		['country' => 'Armenia', 'labels' => [['Հատուկ կարևորության'],['Հույժ գաղտնի'],['Գաղտնի'],['Ծառայողական օգտագործման համար']]],
		['country' => 'Australia', 'labels' => [['Top Secret'], ['Secret'], ['Retired. Treat as Secret.'],['Protected']]],
		['country' => 'Austria', 'labels' => [['Streng Geheim'],['Geheim'],['Vertraulich'],['Eingeschränkt']]],
		['country' => 'Belgium', 'labels' => [['Zeer Geheim','Très Secret'],['Geheim','Secret'],['Vertrouwelijk','Confidentiel'],['Beperkte Verspreiding','Diffusion restreinte']]],
		['country' => 'Bolivia', 'labels' => [['Supersecreto', 'Muy Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		['country' => 'Bosnia and Herzegovina', 'labels' => [['Vrlo tajno'],['Tajno'],['Povjerljivo'],['Interno']]],
		['country' => 'Brazil', 'labels' => [['Ultrassecreto'],['Secreto'],['Confidencial'],['Reservado']]],
		['country' => 'Bulgaria', 'labels' => [['Strògo sèkretno', 'Строго секретно'], ['Sèkretno','Секретно'], ['Poveritèlno','Поверително'], ['Za služebno polzvàne','За служебно ползване']]],
		['country' => 'Cambodia', 'labels' => [['Sam Ngat Bamphot'],['Sam Ngat Roeung'],['Art Kambang'],['Ham Kom Psay']]],
		['country' => 'Canada', 'labels' => [['Top Secret', 'Très secret'],['Secret'],['Confidential','Confidentiel'],['Protected A','Protected B','Protected C', 'Protégé A', 'Protégé B', 'Protégé C']]],
		[ 'country' => 'Chile', 'labels' => [['Secreto'],['Secreto'],['Reservado'],['Reservado']]],
		[ 'country' => 'China', 'labels' => [['Juémì (绝密)'],['Jīmì (机密)'],['Mìmì (秘密)'],['(内部)']]],
		[ 'country' => 'Colombia', 'labels' => [['Ultrasecreto'],['Secreto'],['Confidencial'],['Reserva del sumario']]],
		[ 'country' => 'Costa Rica', 'labels' => [['Alto Secreto'],['Secreto'],['Confidencial'],['']]],
		[ 'country' => 'Croatia', 'labels' => [['Vrlo tajno'],['Tajno'],['Povjerljivo'],['Ograničeno']]],
		[ 'country' => 'Czech Republic', 'labels' => [['Přísně tajné'],['Tajné'],['Důvěrné'],['Vyhrazené']]],
		[ 'country' => 'Singapore', 'labels' => [['Top Secret'],['Secret'],['Confidential'],['Restricted']]],
		[ 'country' => 'Somalia', 'labels' => [['Sir Muhiim ah'],['Sir Gooniya'],['Xog Qarsoon'],['Qarsoon']]],
		[ 'country' => 'Slovak Republic', 'labels' => [['Prísne tajné'],['Tajné'],['Dôverné'],['Vyhradené']]],
		[ 'country' => 'Slovenia', 'labels' => [['Strogo tajno'],['Tajno'],['Zaupno'],['Interno']]],
		[ 'country' => 'Spain', 'labels' => [['Secreto'],['Reservado'],['Confidencial'],['Difusión Limitada']]],
		[ 'country' => 'Sri Lanka', 'labels' => [['අති රහස්‍ය'],['රහස්‍ය'],['රහසිගත'],['සීමාන්විත']]],
		[ 'country' => 'Sweden', 'labels' => [['Kvalificerat hemlig','Hemlig/Top Secret'],['Hemlig','Hemlig/Secret'],['Konfidentiell','Hemlig/Confidential'],['Begränsat hemlig','Hemlig/Restricted']]],
		[ 'country' => 'Switzerland', 'labels' => [[],['Geheim','Secret'],['Vertraulich','Confidentiel'],['Intern','Interne']]],
		[ 'country' => 'Taiwan', 'labels' => [['Top Secret','絕對機密'],['Secret','極機密'],['Confidential','機密'],[]]],
		[ 'country' => 'Tanzania (Swahili)', 'labels' => [['Siri Kuu'],['Siri'],['Stiri'],['Imezuiliwa']]],
		[ 'country' => 'Thailand', 'labels' => [['Lap thi sut ลับที่สุด)'],['Lap mak ลับมาก)'],['Lap ลับ)'],['Pok pit ปกปิด)']]],
		[ 'country' => 'Turkey', 'labels' => [['Çok Gizli'],['Gizli'],['Özel'],['Hizmete Özel']]],
		[ 'country' => 'South Afric', 'labels' => [['Top Secret', 'Uiters Geheim'],['Secret', 'Geheim'],['Confidential', 'Vertroulik'],['Restricted', 'Beperk']]],
		[ 'country' => 'Ukraine', 'labels' => [['Цілком таємно'],['Таємно'],['Конфіденційно'],['Для службового користування']]],
		[ 'country' => 'United Kingdom', 'labels' => [['Top Secret'],['Secret'],['Official-Sensitive', 'Confidential'],['Official', 'Restricted']]],
		[ 'country' => 'United States', 'labels' => [['Top Secret'],['Secret'],['Confidential'],[]]],
		[ 'country' => 'Uruguay', 'labels' => [['Ultra Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Vietnam', 'labels' => [['Tuyệt Mật','絕密'],['Tối Mật', '最密'],['Mật', '密'], ['Phổ Biến Hạn Chế', '普遍限制']]],
		[ 'country' => 'El Salvador', 'labels' => [['Ultra Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Estonia', 'labels' => [['Täiesti salajane'],['Salajane'],['Konfidentsiaalne'],['Piiratud']]],
		[ 'country' => 'Ethiopia', 'labels' => [['ብርቱ ምስጢር'],['ምስጢር'],['ጥብቅ'],['ክልክል']]],
		[ 'country' => 'European Union (EU)', 'labels' => [['Tres Secret UE', 'EU Top Secret'],['Secret UE', 'EU Secret'],['Confidentiel', 'EU Confidential'],['Restreint UE', 'EU Restricted']]],
		[ 'country' => 'European Union (Western) (WEU)', 'labels' => [['Focal top secret'],['WEU Secret'],['WEU Confidential'],['WEU Restricted']]],
		[ 'country' => 'Euratom', 'labels' => [['EURA Top Secret'],['EURA Secret'],['EURA Confidential'],['EURA Restricted']]],
		[ 'country' => 'Finland', 'labels' => [['Erittäin salainen (ST I)'],['Salainen (ST II)'],['Luottamuksellinen (ST III)'],['Käyttö rajoitettu (ST IV)']]],
		[ 'country' => 'France', 'labels' => [['Très secret'],['Secret'],['Secret'],['Diffusion restreinte']]],
		[ 'country' => 'Germany', 'labels' => [['Streng Geheim'],['Geheim'],['VS-Vertraulich'],['VS-Nur Für Den Dienstgebrauch']]],
		[ 'country' => 'Guatemala', 'labels' => [['Alto Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Haiti', 'labels' => [['Top Secret'],['Secret'],['Confidential'],['Reserve']]],
		[ 'country' => 'Honduras', 'labels' => [['Super Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Hong Kong', 'labels' => [['Top Secret', '高度機密'],['Secret', '機密'],['Conial', '保密'],['Restricted', '內部文件/限閱文件']]],
		[ 'country' => 'Hungary', 'labels' => [['Szigorúan Titkos'],['Titkos'],['Bizalmas'],['Korlátozott Terjesztésű']]],
		[ 'country' => 'India (Hindi)', 'labels' => [['परम गुप्त', 'Param Gupt', 'Top Secret'],['गुप्त', 'Gupt', 'Secret'],['गोपनीय', 'Gopniya', 'Confidential'],['प्रतिबंधित/सीमित', 'Pratibandhit', 'seemit', 'Restricted']]],
		[ 'country' => 'Indonesia', 'labels' => [['Sangat Rahasia'],['Rahasia'],['Rahasia Dinas'],['Terbatas']]],
		[ 'country' => 'Iran', 'labels' => [['Bekoli-Serri', 'بکلی سری'],['Serri', 'سری'],['Kheili-Mahramaneh', 'خیلی محرمانه'],['Mahramaneh', 'محرمانه']]],
		[ 'country' => 'Iceland', 'labels' => [['Algert Leyndarmál'],['Leyndarmál'],['Trúnaðarmál'],['Þjónustuskjal']]],
		[ 'country' => 'Ireland (Irish language)', 'labels' => [['An-sicréideach'],['Sicréideach'],['Rúnda'],['Srianta']]],
		[ 'country' => 'South Korea', 'labels' => [['1(Il)-geup Bimil', '1급 비밀, 一級秘密'],['2( Bimil', '2급 비밀, 二級秘密'],['3(Sam)-geup Bimil', '3급 비밀, 三級秘密'],['Daeoebi', '대외비', '對外秘']]],
		[ 'country' => 'Laos', 'labels' => [['Lup Sood Gnod'],['Kuam Lup'],['Kuam Lap'],['Chum Kut Kon Arn']]],
		[ 'country' => 'Latvia', 'labels' => [['Sevišķi slepeni'],['Slepeni'],['Konfidenciāli'],['Dienesta vajadzībām']]],
		[ 'country' => 'Lebanon', 'labels' => [['Tres Secret'],['Secret'],['Confidentiel'],['']]],
		[ 'country' => 'Lithuania', 'labels' => [['Visiškai Slaptai'],['Slaptai'],['Konfidencialiai'],['Riboto Naudojimo']]],
		[ 'country' => 'Malaysia', 'labels' => [['Rahsia Besar'],['Rahsia'],['Sulit'],['Terhad']]],
		[ 'country' => 'Mexico', 'labels' => [['Ultra Secreto'],['Secreto'],['Confidencial'],['Restringido']]],
		[ 'country' => 'Montenegro', 'labels' => [['Strogo Tajno'],['Tajno'],['Povjerljivo'],['Interno']]],
		[ 'country' => 'Netherlands[43]', 'labels' => [['STG. Zeer Geheim'],['STG. Geheim'],['STG. Confidentieel'],['Departementaal Vertrouwelijk']]],
		[ 'country' => 'New Zealand', 'labels' => [['Top Secret'],['Secret'],['Confidential'],['Restricted']]],
		[ 'country' => 'Nicaragua', 'labels' => [['Alto Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Norway', 'labels' => [['Strengt Hemmelig'],['Hemmelig'],['Konfidensielt'],['Begrenset']]],
		[ 'country' => 'Pakistan (English)', 'labels' => [['Top Secret'],['Secret'],['Confidential'],['Restricted']]],
		[ 'country' => 'Paraguay', 'labels' => [['Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Peru', 'labels' => [['Estrictamente Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Italy', 'labels' => [['Segretissimo'],['Segreto'],['Riservatissimo'],['Riservato']]],
		[ 'country' => 'Japan', 'labels' => [['Kimitsu', '機密'],['Gokuhi', '極秘'],['Hi', '秘)'],['tsukaichuui', '取り扱い注意)']]],
		[ 'country' => 'Poland', 'labels' => [['Ściśle tajne'],['Tajne'],['Poufne'],['Zastrzeżone']]],
		[ 'country' => 'Portugal', 'labels' => [['Muito Secreto'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Romania', 'labels' => [['Strict Secret de Importanță Deosebită'],['Strict Secret'],['Secret'],['Secret de serviciu']]],
		[ 'country' => 'Saudi Arabia', 'labels' => [['Saudi Top Secret'],['Saudi Very Secret'],['Saudi Secret'],['Saudi Restricted']]],
		[ 'country' => 'Greece', 'labels' => [['Άκρως Απόρρητον'],['Απόρρητον'],['Εμπιστευτικόν'],['Περιορισμένης, Χρήσης']]],
		[ 'country' => 'Denmark', 'labels' => [['Yderst Hemmeligt (YHM)'],['Hemmeligt (HEM)'],['Fortroligt (FTR)'],['Til Tjenestebrug (TTJ)', 'Fortroligt']]],
		[ 'country' => 'Ecuador', 'labels' => [['Secretisimo'],['Secreto'],['Confidencial'],['Reservado']]],
		[ 'country' => 'Egypt', 'labels' => [['Sirriy lil-Ġāyah', 'سري للغاية'],['Sirriy Ǧiddan','سري جداً'],['Khāṣ','خاص'],['Maḥzūr','محظور']]],
		[ 'country' => 'Iraq', 'labels' => [['Sirriy lil-Ġāyah','سري للغاية'],['Sirriy','سري'],['Khāṣ','خاص'],['Maḥdūd','محدود']]],
		[ 'country' => 'Israel', 'labels' => [['Sodi Beyoter','סודי ביותר'],['Sodi','סודי'],['Shamur','שמור'],['Mugbal','מוגבל']]],
		[ 'country' => 'Jordan', 'labels' => [['Maktūm Ǧiddan','مكتوم جداً'],['Maktūm','مكتوم'],['Sirriy','سري'],['Maḥdūd','محدود']]],
		[ 'country' => 'Pakistan (Urdu)', 'labels' => [['Intahai Khufia','انتہائی خفیہ'],['Khufia','خفیہ'],['Sigh-e-Raz','صیخہ راز'],['Barai Mahdud Taqsim','محدود تقسیم']]],
		[ 'country' => 'Philippines', 'labels' => [['Matinding Lihim'], ['Mahigpit na Lihim'], ['Lihim'],['Ipinagbabawal']]],
		[ 'country' => 'Russia', 'labels' => [['Особой важности','вариант: Совершенно Секретно'],['Совершенно секретно','вариант: Секретно'],['Секретно','вариант: Не подлежит оглашению (Конфиденциально)'],['Для Служебного Пользования (ДСП)']]],
		[ 'country' => 'Serbia', 'labels' => [['Државна тајна','Državna tajna'],['Строго поверљиво','Strogo poverljivo'],['Поверљиво','Poverljivo'],['Интерно','Interno']]],
	];
}
