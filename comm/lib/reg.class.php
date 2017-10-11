<?php
/*
php 随机生成用户名,密码功能,为注册服务
2011/10/24 by ziv
*/

class comm_reg
{
	//取随机用户名
	static function get_rand_user_name()
	{ 
		
		$s = self::get_rand_abc(1);
		$s .= substr(self::get_rand_en(),0,6);
		$s .= rand(1,9);
		$s .= self::get_rand_abc(1);
		$s .= substr(time(),-4);
		//$s .= substr(strtolower(md5(microtime()+rand(1,99999))),5,2);
		$s .= substr(microtime(),2,5);
		
		/*
		$s= self::get_rand_en();
		$s.= self::get_rand_en();
		$s.= self::get_rand_npy();
		$s.= substr(time(),-2);
		*/
		//$s.= substr(time(),-2);
		//$s.= self::get_rand_en();
		//if(rand(1,10)>5) $s.= self::get_rand_npy();
		//$s = substr($s,0,17);
		return $s;    
	} 

	//取随机密码
	static function get_rand_pwd()
	{ 
		$s=substr(self::get_rand_en(),0,6);
		$s.=self::get_rand_str(6); 
		return $s;
	}
	
	//随机取不存在的拼音字
	static function get_rand_npy(){
	
		$s = array();
		$s[] = 'hue';
		$s[] = 'yuang';
		$s[] = 'bue';
		$s[] = 'pue';
		$s[] = 'mue';
		$s[] = 'fue';
		$s[] = 'due';
		$s[] = 'tue';
		$s[] = 'nue';
		$s[] = 'lue';
		$s[] = 'gue';
		$s[] = 'kue';
		$s[] = 'zue';
		$s[] = 'cue';
		$s[] = 'sue';
		$s[] = 'zhue';
		$s[] = 'chue';
		$s[] = 'shue';
		$s[] = 'rue';
		$s[] = 'biu';
		$s[] = 'piu';
		$s[] = 'miu';
		$s[] = 'fiu';
		$s[] = 'tiu';
		$s[] = 'giu';
		$s[] = 'kiu';
		$s[] = 'xiu';
		$s[] = 'ziu';
		$s[] = 'ciu';
		$s[] = 'siu';
		$s[] = 'zhiu';
		$s[] = 'chiu';
		$s[] = 'shiu';
		$s[] = 'riu';
		$s[] = 'wiu';
		$s[] = 'yiu'; 
		$s[] = 'bun';
		$s[] = 'pun';
		$s[] = 'mun';
		$s[] = 'fun';
		$s[] = 'dun';
		$s[] = 'tun';
		$s[] = 'nun';
		$s[] = 'gun';
		$s[] = 'kun';
		$s[] = 'hun';
		$s[] = 'qun';
		$s[] = 'zun';
		$s[] = 'cun';
		$s[] = 'sun';
		$s[] = 'zhun';
		$s[] = 'chun';
		$s[] = 'shun';
		$s[] = 'wun';
		$s[] = 'fie';
		$s[] = 'gie';
		$s[] = 'kie';
		$s[] = 'hie';
		$s[] = 'zie';
		$s[] = 'cie';
		$s[] = 'sie';
		$s[] = 'zhie';
		$s[] = 'chie';
		$s[] = 'shie';
		$s[] = 'rie';
		$s[] = 'wie';
		$s[] = 'yie';
		/*
		$s[] = 'b'; 
		$s[] = 'p';
		$s[] = 'm';
		$s[] = 'f';
		$s[] = 'd';
		$s[] = 't';
		$s[] = 'n';
		$s[] = 'g';
		$s[] = 'k';
		$s[] = 'h';
		$s[] = 'j';
		$s[] = 'q';
		$s[] = 'x';
		$s[] = 'z';
		$s[] = 'c';
		$s[] = 's';
		$s[] = 'zh';
		$s[] = 'ch';
		$s[] = 'sh';
		$s[] = 'r';
		$s[] = 'w';
		$s[] = 'y';
*/
			
		$c=count($s);
		$k=rand(0,$c-1);
		return $s[$k];      
	}

	//随机26字母
	static function get_rand_abc($n,$n2=null){
		if($n2)
		{
			$n2=intval($n2);
			$n=rand($n-$n2,$n+$n2);	
		}

		$s = array();
		$s[] = 'a';
		$s[] = 'b';
		$s[] = 'c';
		$s[] = 'd';
		$s[] = 'e';
		$s[] = 'f';
		$s[] = 'g';
		$s[] = 'h';
		$s[] = 'i';
		$s[] = 'j';
		$s[] = 'k';
		$s[] = 'l';
		$s[] = 'm';
		$s[] = 'n';
		$s[] = 'o';
		$s[] = 'p';
		$s[] = 'q';
		$s[] = 'r';
		$s[] = 's';
		$s[] = 't';
		$s[] = 'u';
		$s[] = 'v';
		$s[] = 'w';
		$s[] = 'x';
		$s[] = 'y';
		$s[] = 'z';
			
		$str = '';
		for($i=0;$i<$n;$i++){
			$tmp = array_rand($s,1);
			$str .= $s[$tmp];
		}
		return $str;
	}

	static function get_rand_str($n=5)
	{ 
		$n=intval($n);
		$s=md5(microtime());
		return substr($s,0,$n);    
	}

	static function get_rand_num($n=5)
	{ 
		$n=intval($n);
		//$s = '_';
		for($i=0;$i<$n;$i++){
			$s .= rand(0,9);
		}
		return $s;
	}
	
	//取随机英文单词生成英语标题
	static function get_english_tit()
	{ 
		$n=rand(5,7);
		for($i=0;$i<$n;$i++){ 
			$tit .= self::get_rand_en().' ';
		}
		return trim($tit);
	}
	
	//取随机英文单词 1000个
	static function get_rand_en()
	{ 
		$a=null; 
		$a[0] = "able";
		$a[1] = "about";
		$a[2] = "above";
		$a[3] = "afraid";
		$a[4] = "after";
		$a[5] = "afternoon";
		$a[6] = "again";
		$a[7] = "age";
		$a[8] = "ago";
		$a[9] = "agree";
		$a[10] = "air";
		$a[11] = "airplane";
		$a[12] = "airport";
		$a[13] = "all";
		$a[14] = "almost";
		$a[15] = "along";
		$a[16] = "already";
		$a[17] = "also";
		$a[18] = "always";
		$a[19] = "American";
		$a[20] = "and";
		$a[21] = "angry";
		$a[22] = "animal";
		$a[23] = "another";
		$a[24] = "answer";
		$a[25] = "any";
		$a[26] = "anyone";
		$a[27] = "anything";
		$a[28] = "apartment";
		$a[29] = "appear";
		$a[30] = "apple";
		$a[31] = "April";
		$a[32] = "arm";
		$a[33] = "around";
		$a[34] = "arrive";
		$a[35] = "art";
		$a[36] = "as";
		$a[37] = "ask";
		$a[38] = "August";
		$a[39] = "aunt";
		$a[40] = "autumn";
		$a[41] = "away";
		$a[42] = "baby";
		$a[43] = "back";
		$a[44] = "bad";
		$a[45] = "bag";
		$a[46] = "bakery";
		$a[47] = "ball";
		$a[48] = "banana";
		$a[49] = "band";
		$a[50] = "bank";
		$a[51] = "baseball";
		$a[52] = "basket";
		$a[53] = "basketball";
		$a[54] = "bath";
		$a[55] = "bathroom";
		$a[56] = "be";
		$a[57] = "bear";
		$a[58] = "beautiful";
		$a[59] = "because";
		$a[60] = "become";
		$a[61] = "bed";
		$a[62] = "bedroom";
		$a[63] = "bee";
		$a[64] = "beef";
		$a[65] = "before";
		$a[66] = "begin";
		$a[67] = "behind";
		$a[68] = "believe";
		$a[69] = "bell";
		$a[70] = "belong";
		$a[71] = "below";
		$a[72] = "belt";
		$a[73] = "beside";
		$a[74] = "between";
		$a[75] = "bicycle";
		$a[76] = "bird";
		$a[77] = "birthday";
		$a[78] = "bite";
		$a[79] = "black";
		$a[80] = "blackboard";
		$a[81] = "blind";
		$a[82] = "block";
		$a[83] = "blow";
		$a[84] = "blue";
		$a[85] = "boat";
		$a[86] = "body";
		$a[87] = "book";
		$a[88] = "bookstore";
		$a[89] = "bored";
		$a[90] = "boring";
		$a[91] = "born";
		$a[92] = "borrow";
		$a[93] = "boss";
		$a[94] = "both";
		$a[95] = "bottom";
		$a[96] = "bowl";
		$a[97] = "box";
		$a[98] = "boy";
		$a[99] = "bread";
		$a[100] = "break";
		$a[101] = "breakfast";
		$a[102] = "bridge";
		$a[103] = "bright";
		$a[104] = "bring";
		$a[105] = "brother";
		$a[106] = "brown";
		$a[107] = "brush";
		$a[108] = "build";
		$a[109] = "burn";
		$a[110] = "bus";
		$a[111] = "business";
		$a[112] = "businessman";
		$a[113] = "busy";
		$a[114] = "butter";
		$a[115] = "buy";
		$a[116] = "by";
		$a[117] = "cake";
		$a[118] = "call";
		$a[119] = "camera";
		$a[120] = "camp";
		$a[121] = "can";
		$a[122] = "candy";
		$a[123] = "cap";
		$a[124] = "car";
		$a[125] = "card";
		$a[126] = "care";
		$a[127] = "careful";
		$a[128] = "carry";
		$a[129] = "case";
		$a[130] = "cat";
		$a[131] = "catch";
		$a[132] = "celebrate";
		$a[133] = "center";
		$a[134] = "chair";
		$a[135] = "chalk";
		$a[136] = "chance";
		$a[137] = "change";
		$a[138] = "cheap";
		$a[139] = "cheat";
		$a[140] = "check";
		$a[141] = "cheer";
		$a[142] = "cheese";
		$a[143] = "chicken";
		$a[144] = "child";
		$a[145] = "China";
		$a[146] = "Chinese";
		$a[147] = "chocolate";
		$a[148] = "Christmas";
		$a[149] = "church";
		$a[150] = "circle";
		$a[151] = "class";
		$a[152] = "classmate";
		$a[153] = "classroom";
		$a[154] = "clean";
		$a[155] = "clear";
		$a[156] = "climb";
		$a[157] = "clock";
		$a[158] = "close";
		$a[159] = "clothes";
		$a[160] = "cloudy";
		$a[161] = "club";
		$a[162] = "coat";
		$a[163] = "coffee";
		$a[164] = "Coke";
		$a[165] = "cold";
		$a[166] = "collect";
		$a[167] = "color";
		$a[168] = "come";
		$a[169] = "comfortable";
		$a[170] = "common";
		$a[171] = "computer";
		$a[172] = "convenient";
		$a[173] = "cook";
		$a[174] = "cookie";
		$a[175] = "cool";
		$a[176] = "copy";
		$a[177] = "correct";
		$a[178] = "cost";
		$a[179] = "couch";
		$a[180] = "count";
		$a[181] = "country";
		$a[182] = "cousin";
		$a[183] = "cover";
		$a[184] = "cow";
		$a[185] = "crazy";
		$a[186] = "cross";
		$a[187] = "cry";
		$a[188] = "cup";
		$a[189] = "cute";
		$a[190] = "dance";
		$a[191] = "dangerous";
		$a[192] = "dark";
		$a[193] = "date";
		$a[194] = "daughter";
		$a[195] = "day";
		$a[196] = "dead";
		$a[197] = "dear";
		$a[198] = "December";
		$a[199] = "decide";
		$a[200] = "delicious";
		$a[201] = "department";
		$a[202] = "desk";
		$a[203] = "dictionary";
		$a[204] = "die";
		$a[205] = "different";
		$a[206] = "difficult";
		$a[207] = "dig";
		$a[208] = "dinner";
		$a[209] = "dirty";
		$a[210] = "dish";
		$a[211] = "do";
		$a[212] = "doctor";
		$a[213] = "dog";
		$a[214] = "doll";
		$a[215] = "dollar";
		$a[216] = "door";
		$a[217] = "down";
		$a[218] = "dozen";
		$a[219] = "draw";
		$a[220] = "dream";
		$a[221] = "dress";
		$a[222] = "drink";
		$a[223] = "drive";
		$a[224] = "driver";
		$a[225] = "drop";
		$a[226] = "dry";
		$a[227] = "email";
		$a[228] = "each";
		$a[229] = "ear";
		$a[230] = "early";
		$a[231] = "earth";
		$a[232] = "east";
		$a[233] = "easy";
		$a[234] = "eat";
		$a[235] = "egg";
		$a[236] = "eight";
		$a[237] = "eighteen";
		$a[238] = "eighth";
		$a[239] = "eighty";
		$a[240] = "either";
		$a[241] = "elephant";
		$a[242] = "eleven";
		$a[243] = "else";
		$a[244] = "end";
		$a[245] = "enjoy";
		$a[246] = "enough";
		$a[247] = "enter";
		$a[248] = "eraser";
		$a[249] = "eve";
		$a[250] = "evening";
		$a[251] = "event";
		$a[252] = "ever";
		$a[253] = "every";
		$a[254] = "everyone";
		$a[255] = "everything";
		$a[256] = "example";
		$a[257] = "excellent";
		$a[258] = "except";
		$a[259] = "excited";
		$a[260] = "exciting";
		$a[261] = "excuse";
		$a[262] = "exercise";
		$a[263] = "expensive";
		$a[264] = "eye";
		$a[265] = "face";
		$a[266] = "fact";
		$a[267] = "factory";
		$a[268] = "fall";
		$a[269] = "family";
		$a[270] = "famous";
		$a[271] = "fan";
		$a[272] = "farm";
		$a[273] = "farmer";
		$a[274] = "fast";
		$a[275] = "fat";
		$a[276] = "father";
		$a[277] = "favorite";
		$a[278] = "February";
		$a[279] = "feel";
		$a[280] = "festival";
		$a[281] = "few";
		$a[282] = "fifteen";
		$a[283] = "fifty";
		$a[284] = "fill";
		$a[285] = "finally";
		$a[286] = "find";
		$a[287] = "fine";
		$a[288] = "finger";
		$a[289] = "finish";
		$a[290] = "fire";
		$a[291] = "first";
		$a[292] = "fish";
		$a[293] = "fisherman";
		$a[294] = "five";
		$a[295] = "fix";
		$a[296] = "floor";
		$a[297] = "flower";
		$a[298] = "fly";
		$a[299] = "follow";
		$a[300] = "food";
		$a[301] = "foot";
		$a[302] = "foreign";
		$a[303] = "foreigner";
		$a[304] = "forget";
		$a[305] = "fork";
		$a[306] = "forty";
		$a[307] = "four";
		$a[308] = "fourteen";
		$a[309] = "fourth";
		$a[310] = "free";
		$a[311] = "fresh";
		$a[312] = "Friday";
		$a[313] = "friend";
		$a[314] = "friendly";
		$a[315] = "from";
		$a[316] = "front";
		$a[317] = "fruit";
		$a[318] = "full";
		$a[319] = "fun";
		$a[320] = "funny";
		$a[321] = "game";
		$a[322] = "garbage";
		$a[323] = "garden";
		$a[324] = "gas";
		$a[325] = "get";
		$a[326] = "gift";
		$a[327] = "girl";
		$a[328] = "give";
		$a[329] = "glad";
		$a[330] = "glass";
		$a[331] = "glove";
		$a[332] = "go";
		$a[333] = "goat";
		$a[334] = "good";
		$a[335] = "goodbye";
		$a[336] = "grade";
		$a[337] = "grandfather";
		$a[338] = "grandmother";
		$a[339] = "grass";
		$a[340] = "great";
		$a[341] = "green";
		$a[342] = "ground";
		$a[343] = "group";
		$a[344] = "grow";
		$a[345] = "guess";
		$a[346] = "habit";
		$a[347] = "hair";
		$a[348] = "half";
		$a[349] = "ham";
		$a[350] = "hamburger";
		$a[351] = "hand";
		$a[352] = "handsome";
		$a[353] = "happen";
		$a[354] = "happy";
		$a[355] = "hard";
		$a[356] = "hardworking";
		$a[357] = "hat";
		$a[358] = "hate";
		$a[359] = "he";
		$a[360] = "head";
		$a[361] = "headache";
		$a[362] = "health";
		$a[363] = "healthy";
		$a[364] = "hear";
		$a[365] = "heart";
		$a[366] = "heat";
		$a[367] = "heavy";
		$a[368] = "hello";
		$a[369] = "help";
		$a[370] = "helpful";
		$a[371] = "here";
		$a[372] = "hi";
		$a[373] = "hide";
		$a[374] = "high";
		$a[375] = "hill";
		$a[376] = "history";
		$a[377] = "hit";
		$a[378] = "hold";
		$a[379] = "holiday";
		$a[380] = "home";
		$a[381] = "homework";
		$a[382] = "honest";
		$a[383] = "hope";
		$a[384] = "horse";
		$a[385] = "hospital";
		$a[386] = "hot";
		$a[387] = "hot";
		$a[388] = "hotel";
		$a[389] = "hour";
		$a[390] = "house";
		$a[391] = "how";
		$a[392] = "however";
		$a[393] = "hundred";
		$a[394] = "hungry";
		$a[395] = "hurry";
		$a[396] = "hurt";
		$a[397] = "Ican";
		$a[398] = "icecream";
		$a[399] = "ice";
		$a[400] = "idea";
		$a[401] = "ifu";
		$a[402] = "important";
		$a[403] = "inurs";
		$a[404] = "inside";
		$a[405] = "interest";
		$a[406] = "interested";
		$a[407] = "interesting";
		$a[408] = "Internet";
		$a[409] = "into";
		$a[410] = "island";
		$a[411] = "it";
		$a[412] = "jacket";
		$a[413] = "January";
		$a[414] = "jeans";
		$a[415] = "job";
		$a[416] = "join";
		$a[417] = "joy";
		$a[418] = "juice";
		$a[419] = "July";
		$a[420] = "jump";
		$a[421] = "June";
		$a[422] = "just";
		$a[423] = "keep";
		$a[424] = "key";
		$a[425] = "kick";
		$a[426] = "kid";
		$a[427] = "kill";
		$a[428] = "kilogram";
		$a[429] = "kind";
		$a[430] = "king";
		$a[431] = "kiss";
		$a[432] = "kitchen";
		$a[433] = "kite";
		$a[434] = "knife";
		$a[435] = "knock";
		$a[436] = "know";
		$a[437] = "knowledge";
		$a[438] = "lake";
		$a[439] = "lamp";
		$a[440] = "land";
		$a[441] = "language";
		$a[442] = "large";
		$a[443] = "last";
		$a[444] = "late";
		$a[445] = "later";
		$a[446] = "laugh";
		$a[447] = "lazy";
		$a[448] = "lead";
		$a[449] = "leader";
		$a[450] = "learn";
		$a[451] = "least";
		$a[452] = "leave";
		$a[453] = "leg";
		$a[454] = "lemon";
		$a[455] = "lend";
		$a[456] = "less";
		$a[457] = "lesson";
		$a[458] = "let";
		$a[459] = "letter";
		$a[460] = "library";
		$a[461] = "lie";
		$a[462] = "life";
		$a[463] = "light";
		$a[464] = "like";
		$a[465] = "line";
		$a[466] = "lion";
		$a[467] = "lip";
		$a[468] = "list";
		$a[469] = "listen";
		$a[470] = "little";
		$a[471] = "live";
		$a[472] = "lonely";
		$a[473] = "long";
		$a[474] = "look";
		$a[475] = "lose";
		$a[476] = "loud";
		$a[477] = "love";
		$a[478] = "low";
		$a[479] = "lucky";
		$a[480] = "lunch";
		$a[481] = "machine";
		$a[482] = "magic";
		$a[483] = "mail";
		$a[484] = "mailman";
		$a[485] = "make";
		$a[486] = "man";
		$a[487] = "many";
		$a[488] = "map";
		$a[489] = "March";
		$a[490] = "mark";
		$a[491] = "married";
		$a[492] = "math";
		$a[493] = "matter";
		$a[494] = "may";
		$a[495] = "maybe";
		$a[496] = "meal";
		$a[497] = "mean";
		$a[498] = "meat";
		$a[499] = "medicine";
		$a[500] = "medium";
		$a[501] = "meet";
		$a[502] = "meeting";
		$a[503] = "menu";
		$a[504] = "mile";
		$a[505] = "milk";
		$a[506] = "million";
		$a[507] = "mind";
		$a[508] = "minute";
		$a[509] = "miss";
		$a[510] = "modern";
		$a[511] = "moment";
		$a[512] = "Monday";
		$a[513] = "money";
		$a[514] = "monkey";
		$a[515] = "month";
		$a[516] = "moon";
		$a[517] = "more";
		$a[518] = "morning";
		$a[519] = "most";
		$a[520] = "mother";
		$a[521] = "motorcycle";
		$a[522] = "mountain";
		$a[523] = "mouse";
		$a[524] = "mouth";
		$a[525] = "move";
		$a[526] = "movie";
		$a[527] = "mr";
		$a[528] = "MRS";
		$a[529] = "much";
		$a[530] = "museum";
		$a[531] = "music";
		$a[532] = "must";
		$a[533] = "name";
		$a[534] = "national";
		$a[535] = "near";
		$a[536] = "neck";
		$a[537] = "need";
		$a[538] = "never";
		$a[539] = "new";
		$a[540] = "news";
		$a[541] = "next";
		$a[542] = "nice";
		$a[543] = "night";
		$a[544] = "nine";
		$a[545] = "nineteen";
		$a[546] = "ninety";
		$a[547] = "ninth";
		$a[548] = "nobody";
		$a[549] = "nod";
		$a[550] = "noise";
		$a[551] = "noodle";
		$a[552] = "noon";
		$a[553] = "north";
		$a[554] = "nose";
		$a[555] = "not";
		$a[556] = "notebook";
		$a[557] = "nothing";
		$a[558] = "notice";
		$a[559] = "November";
		$a[560] = "now";
		$a[561] = "number";
		$a[562] = "nurse";
		$a[563] = "clock";
		$a[564] = "October";
		$a[565] = "of";
		$a[566] = "off";
		$a[567] = "officer";
		$a[568] = "often";
		$a[569] = "oil";
		$a[570] = "old";
		$a[571] = "on";
		$a[572] = "once";
		$a[573] = "one";
		$a[574] = "only";
		$a[575] = "open";
		$a[576] = "or";
		$a[577] = "orange";
		$a[578] = "order";
		$a[579] = "other";
		$a[580] = "out";
		$a[581] = "outside";
		$a[582] = "over";
		$a[583] = "own";
		$a[584] = "pack";
		$a[585] = "package";
		$a[586] = "paint";
		$a[587] = "pair";
		$a[588] = "pants";
		$a[589] = "paper";
		$a[590] = "parent";
		$a[591] = "park";
		$a[592] = "part";
		$a[593] = "party";
		$a[594] = "pass";
		$a[595] = "past";
		$a[596] = "pay";
		$a[597] = "PE";
		$a[598] = "pen";
		$a[599] = "pencil";
		$a[600] = "people";
		$a[601] = "perhaps";
		$a[602] = "person";
		$a[603] = "pet";
		$a[604] = "piano";
		$a[605] = "picnic";
		$a[606] = "picture";
		$a[607] = "pie";
		$a[608] = "piece";
		$a[609] = "pig";
		$a[610] = "pink";
		$a[611] = "pizza";
		$a[612] = "place";
		$a[613] = "plan";
		$a[614] = "play";
		$a[615] = "player";
		$a[616] = "playground";
		$a[617] = "please";
		$a[618] = "point";
		$a[619] = "police";
		$a[620] = "polite";
		$a[621] = "poor";
		$a[622] = "popcorn";
		$a[623] = "popular";
		$a[624] = "possible";
		$a[625] = "post";
		$a[626] = "postcard";
		$a[627] = "pound";
		$a[628] = "practice";
		$a[629] = "prepare";
		$a[630] = "present";
		$a[631] = "pretty";
		$a[632] = "price";
		$a[633] = "problem";
		$a[634] = "program";
		$a[635] = "proud";
		$a[636] = "public";
		$a[637] = "pull";
		$a[638] = "purple";
		$a[639] = "push";
		$a[640] = "put";
		$a[641] = "queen";
		$a[642] = "question";
		$a[643] = "quiet";
		$a[644] = "quite";
		$a[645] = "rabbit";
		$a[646] = "radio";
		$a[647] = "railway";
		$a[648] = "rain";
		$a[649] = "rainbow";
		$a[650] = "rainy";
		$a[651] = "read";
		$a[652] = "ready";
		$a[653] = "real";
		$a[654] = "really";
		$a[655] = "red";
		$a[656] = "refrigerator";
		$a[657] = "remember";
		$a[658] = "repeat";
		$a[659] = "rest";
		$a[660] = "restaurant";
		$a[661] = "rice";
		$a[662] = "ride";
		$a[663] = "right";
		$a[664] = "ring";
		$a[665] = "river";
		$a[666] = "road";
		$a[667] = "rocs";
		$a[668] = "room";
		$a[669] = "rose";
		$a[670] = "round";
		$a[671] = "rule";
		$a[672] = "ruler";
		$a[673] = "run";
		$a[674] = "usad";
		$a[675] = "safe";
		$a[676] = "salad";
		$a[677] = "sale";
		$a[678] = "salt";
		$a[679] = "same";
		$a[680] = "sandwich";
		$a[681] = "save";
		$a[682] = "say";
		$a[683] = "school";
		$a[684] = "sea";
		$a[685] = "season";
		$a[686] = "seat";
		$a[687] = "second";
		$a[688] = "see";
		$a[689] = "seldom";
		$a[690] = "sell";
		$a[691] = "send";
		$a[692] = "sentence";
		$a[693] = "September";
		$a[694] = "serious";
		$a[695] = "seven";
		$a[696] = "seventeen";
		$a[697] = "seventh";
		$a[698] = "seventy";
		$a[699] = "shall";
		$a[700] = "shape";
		$a[701] = "share";
		$a[702] = "she";
		$a[703] = "sheep";
		$a[704] = "ship";
		$a[705] = "shirt";
		$a[706] = "shoe";
		$a[707] = "shop";
		$a[708] = "shopkeeper";
		$a[709] = "short";
		$a[710] = "should";
		$a[711] = "shoulder";
		$a[712] = "show";
		$a[713] = "shy";
		$a[714] = "sick";
		$a[715] = "side";
		$a[716] = "sidewalk";
		$a[717] = "since";
		$a[718] = "sing";
		$a[719] = "singer";
		$a[720] = "sir";
		$a[721] = "sister";
		$a[722] = "sit";
		$a[723] = "six";
		$a[724] = "sixteen";
		$a[725] = "sixth";
		$a[726] = "sixty";
		$a[727] = "size";
		$a[728] = "skirt";
		$a[729] = "sky";
		$a[730] = "sleep";
		$a[731] = "slow";
		$a[732] = "small";
		$a[733] = "smart";
		$a[734] = "smell";
		$a[735] = "smile";
		$a[736] = "snack";
		$a[737] = "snake";
		$a[738] = "snow";
		$a[739] = "so";
		$a[740] = "sock";
		$a[741] = "sofa";
		$a[742] = "some";
		$a[743] = "someone";
		$a[744] = "something";
		$a[745] = "sometimes";
		$a[746] = "somewhere";
		$a[747] = "son";
		$a[748] = "song";
		$a[749] = "soon";
		$a[750] = "sorry";
		$a[751] = "sound";
		$a[752] = "soup";
		$a[753] = "south";
		$a[754] = "space";
		$a[755] = "special";
		$a[756] = "spell";
		$a[757] = "spend";
		$a[758] = "spoon";
		$a[759] = "sports";
		$a[760] = "spring";
		$a[761] = "square";
		$a[762] = "stand";
		$a[763] = "star";
		$a[764] = "start";
		$a[765] = "station";
		$a[766] = "stay";
		$a[767] = "steak";
		$a[768] = "still";
		$a[769] = "stomach";
		$a[770] = "stop";
		$a[771] = "store";
		$a[772] = "story";
		$a[773] = "strange";
		$a[774] = "street";
		$a[775] = "strong";
		$a[776] = "student";
		$a[777] = "study";
		$a[778] = "stupid";
		$a[779] = "successful";
		$a[780] = "sugar";
		$a[781] = "summer";
		$a[782] = "sun";
		$a[783] = "Sunday";
		$a[784] = "sunny";
		$a[785] = "supermarket";
		$a[786] = "sure";
		$a[787] = "surprise";
		$a[788] = "surprised";
		$a[789] = "sweater";
		$a[790] = "sweet";
		$a[791] = "swim";
		$a[792] = "table";
		$a[793] = "take";
		$a[794] = "talk";
		$a[795] = "tall";
		$a[796] = "tape";
		$a[797] = "taste";
		$a[798] = "taxi";
		$a[799] = "tea";
		$a[800] = "teach";
		$a[801] = "teacher";
		$a[802] = "team";
		$a[803] = "teenager";
		$a[804] = "telephone";
		$a[805] = "television";
		$a[806] = "tell";
		$a[807] = "ten";
		$a[808] = "tennis";
		$a[809] = "tenth";
		$a[810] = "test";
		$a[811] = "than";
		$a[812] = "that";
		$a[813] = "the";
		$a[814] = "theater";
		$a[815] = "then";
		$a[816] = "there";
		$a[817] = "these";
		$a[818] = "they";
		$a[819] = "thin";
		$a[820] = "thing";
		$a[821] = "think";
		$a[822] = "third";
		$a[823] = "thirsty";
		$a[824] = "thirteen";
		$a[825] = "thirty";
		$a[826] = "this";
		$a[827] = "those";
		$a[828] = "though";
		$a[829] = "thousand";
		$a[830] = "three";
		$a[831] = "ticket";
		$a[832] = "tiger";
		$a[833] = "time";
		$a[834] = "tired";
		$a[835] = "to";
		$a[836] = "today";
		$a[837] = "together";
		$a[838] = "tomato";
		$a[839] = "tomorrow";
		$a[840] = "tonight";
		$a[841] = "too";
		$a[842] = "tooth";
		$a[843] = "touch";
		$a[844] = "towel";
		$a[845] = "town";
		$a[846] = "toy";
		$a[847] = "traffic";
		$a[848] = "train";
		$a[849] = "tree";
		$a[850] = "trouble";
		$a[851] = "truck";
		$a[852] = "try";
		$a[853] = "Tuesday";
		$a[854] = "turn";
		$a[855] = "twelve";
		$a[856] = "twenty";
		$a[857] = "two";
		$a[858] = "typhoon";
		$a[859] = "umbrella";
		$a[860] = "uncle";
		$a[861] = "under";
		$a[862] = "understand";
		$a[863] = "unhappy";
		$a[864] = "uniform";
		$a[865] = "until";
		$a[866] = "up";
		$a[867] = "USA";
		$a[868] = "use";
		$a[869] = "usually";
		$a[870] = "vacation";
		$a[871] = "vegetable";
		$a[872] = "very";
		$a[873] = "video";
		$a[874] = "visit";
		$a[875] = "voice";
		$a[876] = "wait";
		$a[877] = "waiter";
		$a[878] = "waitress";
		$a[879] = "wake";
		$a[880] = "walk";
		$a[881] = "wall";
		$a[882] = "want";
		$a[883] = "warm";
		$a[884] = "wash";
		$a[885] = "watch";
		$a[886] = "water";
		$a[887] = "way";
		$a[888] = "weak";
		$a[889] = "wear";
		$a[890] = "weather";
		$a[891] = "Wednesday";
		$a[892] = "week";
		$a[893] = "weekend";
		$a[894] = "welcome";
		$a[895] = "well";
		$a[896] = "west";
		$a[897] = "wet";
		$a[898] = "what";
		$a[899] = "when";
		$a[900] = "where";
		$a[901] = "whether";
		$a[902] = "which";
		$a[903] = "white";
		$a[904] = "who";
		$a[905] = "whose";
		$a[906] = "why";
		$a[907] = "will";
		$a[908] = "win";
		$a[909] = "wind";
		$a[910] = "window";
		$a[911] = "windy";
		$a[912] = "winter";
		$a[913] = "wise";
		$a[914] = "wish";
		$a[915] = "with";
		$a[916] = "without";
		$a[917] = "woman";
		$a[918] = "wonderful";
		$a[919] = "word";
		$a[920] = "work";
		$a[921] = "workbook";
		$a[922] = "worker";
		$a[923] = "world";
		$a[924] = "worry";
		$a[925] = "write";
		$a[926] = "wrong";
		$a[927] = "year";
		$a[928] = "yellow";
		$a[929] = "yes";
		$a[930] = "yesterday";
		$a[931] = "yet";
		$a[932] = "you";
		$a[933] = "young";
		$a[934] = "zero";
		$a[935] = "zoo";
		$c=count($a);
		$k=rand(0,$c-1);
		return $a[$k];     
	}

	//取随机中文姓名
	static function get_chinese_name($sex = null){
		//取得姓
		$family_names = self::family_names();
		$rand = self::s_rand(0, count($family_names)-1);
		$family_name = $family_names[$rand];
		
		//取得昵称的第一个名
		//10%的概率不按性别，去更大的库内选字
		if(self::s_luck(0.1)){
			$dict = self::chinese_name_word_by_sex();
		} else {
			$dict = self::chinese_name_word_by_sex($sex);
		}
		$words_count = mb_strlen($dict, 'UTF8')-1;
		$rand1 = self::s_rand(0,$words_count);
		$nickname = mb_substr($dict, $rand1, 1, 'UTF8');
		
		//概率事件生成第二个字
		if(self::s_luck(0.3)){
			if(self::s_luck(0.1)){
				$dict_2 = self::chinese_name_word_by_sex();
			} else {
				$dict_2 = self::chinese_name_word_by_sex($sex);
			}
			$words_count_2 = mb_strlen($dict_2, 'UTF8')-1;
			$rand2 = self::s_rand(0,$words_count_2);
			$nickname .= mb_substr($dict_2, $rand2, 1, 'UTF8');
		}
		return $family_name.$nickname;
	}

	//计算概率，默认10%
	static function s_luck($percent = 0.2){
		$x = $percent*100;
		$y = 100;
		return (mt_rand(0, $y-1) < $x);
	}
	
	static function s_rand($start = 0, $count){
		return mt_rand($start, $count);
	}

	/**
	 * 百家姓
	 */
	static function family_names(){
		$ary['high'] = array(
			'赵','钱','孙','李','周','吴','郑','王','冯','陈',
			'张','马','刘','许','沈','杨','朱','胡','黄','何',
			'唐','曹','陶','罗','徐','宋','林','谢','严'
		);
		$ary['normal'] = array(
			'褚','卫','蒋','韩','秦','余','鲍','史','费','薛',
			'吕','施','孔','华','金','魏','雷','贺','顾','姜',
			'苏','潘','葛','奚','范','彭','郎','鲁','韦','万',
			'苗','花','方','俞','任','袁','柳','杜','叶','董',
			'郭','梅','姚','于','粱','孟','崔','戚','邹','柏',
			'倪','汤','殷','毕','郝','常','高','丁','邓','窦',
			'傅','齐','康','伍','陆','萧','茅','尹','卞','邵',
			'阮','石','裴','邢','包','汪','霍','邬','卢','田',
			'凌','夏','蔡','戴','程','牛','阎','聂','庄','毛',
			'穆','钟','江'
		);
		$ary['low'] = array(
			'岑','章','祁','骆','舒','洪','侯','郁','单','龚',
			'禹','狄','盛','童','颜','樊','莫','扈','景','那',
			'明','臧','计','成','谈','庞','柯','管','符','关',
			'熊','纪','屈','项','祝','仇','邱','解','伊','栾',
			'闵','席','季','强','贾','路','娄','裘','乌','武',
			'房','甄','翁','荆','荀','甘','詹','屠','翟','赖',
			'闻','习','古','师','巩','辛','晁','沙','虞','申',
			'焦','缪','段','宫','郜','龙','黎','刁','白','姬',
			'滕','云'
		);
		$ary['double'] = array(
			'上官','欧阳','夏侯','东方','皇甫','司徒','公孙',
			'尉迟','司马','诸葛'
		);

		$is_luck = rand(0,10000);

		if($is_luck > 3000 and $is_luck <= 8000){
			return $ary['high'];
		}

		if($is_luck < 500 or ($is_luck > 9500 and $is_luck <= 9999)){
			return $ary['low'];
		}

		if($is_luck == 10000){
			return $ary['double'];
		}

		return $ary['normal'];
		
			/*
			'干','应','宗','宣','富','巫','芮','羿','加','封',
			'杭','诸','左','吉','安','蒲','台','从','苍','双',
			'钮','嵇','滑','荣','危','松','井','鄂','索','咸',
			'羊','於','惠','麴','山','谷','车','宿','扶','堵',
			'储','汲','邴','糜','籍','卓','蔺','胥','能','怀',
			'巴','弓','牧','隗','蓟','薄','印','莘','党','谭',
			'蓬','全','郗','班','仰','秋','仲','宁','冉','宰',
			'暴','钭','厉','戎','祖','米','贡','劳','逄','桑',
			'束','幸','司','韶','贝','伏','郦','雍','郤','璩',
			'桂','濮','寿','通','边','燕','冀','郏','浦','尚',
			'农','温','别','晏','柴','瞿','充','慕','连','茹',
			'宦','艾','鱼','容','向','易','慎','戈','廖','庚',
			'终','暨','居','衡','步','都','耿','满','弘','匡',
			'广','禄','阙','东','殴','殳','国','文','寇','沃',
			'夔','隆','厍','勾','敖','融','利','蔚','越','冷',
			'訾','阚','简','饶','空','曾','毋','乜','养','鞠',
			'须','丰','巢','蒯','相','查','后','红','游','竺',
			'元','皮','蓝','麻','宓','水','时','乐','昌','支',
			'权','逯','盖','益','卜','平','和','贲','尤','喻',
			'经','桓','公','俟','凤','酆','廉','湛','咎',
	
			'闻人','公羊','澹台','公冶','宗政','濮阳',
			'淳于','仲孙','太叔','申屠','乐正','轩辕','令狐','钟离','闾丘',
			'长孙','慕容','鲜于','宇文','司空','亓官','司寇','仉督','子车',
			'颛孙','端木','巫马','公西','漆雕','乐正','壤驷','公良','拓拔','夹谷',
			'宰父','谷粱','晋楚','闫法','汝鄢','涂钦','段干','百里','东郭','南门',
			'呼延','妫海','羊舌','微生','岳帅','缑亢','况後','有琴','梁丘','左丘',
			'东门','西门','商牟','佘佴','伯赏','南宫','墨哈','谯笪','年爱','阳佟',
			*/
	}
	
	//取名常用字，分男女
	static function chinese_name_word_by_sex($sex = null){
		$str_man = '伟刚勇毅俊峰强军平保东文辉力明永健世广志义兴良海山仁波宁贵福生龙元全国胜学祥才发武新利清飞彬富顺信子杰涛昌成康星光天达安岩中茂进林有坚和彪博诚先敬震振壮会思群豪心邦承乐绍功松善厚庆磊民友裕河哲江超浩亮政谦亨奇固之轮翰朗伯宏言若鸣朋斌梁栋维启克伦翔旭鹏泽晨辰士以建家致树炎德行时泰盛雄琛钧冠策腾楠榕风航弘';
		$str_women = '秀娟英华慧巧美娜静淑惠珠翠雅芝玉萍红娥玲芬芳燕彩春菊兰凤洁梅琳素云莲真环雪荣爱妹霞香月莺媛艳瑞凡佳嘉琼勤珍贞莉桂娣叶璧璐娅琦晶妍茜秋珊莎锦黛青倩婷姣婉娴瑾颖露瑶怡婵雁蓓纨仪荷丹蓉眉君琴蕊薇菁梦岚苑婕馨瑗琰韵融园艺咏卿聪澜纯毓悦昭冰爽琬茗羽希宁欣飘育滢馥筠柔竹霭凝晓欢霄枫芸菲寒伊亚宜可姬舒影荔枝思丽';
		
		if($sex == 'man'){
			return $str_man;
		} else if($sex == 'women'){
			return $str_women;
		} else {
			return self::chinese_name_word();
		}
	}
	
	//取名常用字
	static function chinese_name_word(){
		$str = '乙乃了人入刀力卜又几丁上下久个丸乞也于千大子寸小山川工己匀女子中丹之予云井亢介仁元公切分化午升友及太天夫少引心户支文斗斤方日月木火水比丘且世丙主井仕仙代令充冬出加功包北半占卯右可句叶古司只召外本巧巨市布平弘弗必戊旦正民永玉瓦甘生用田由甲申白目石穴立亘交仰任仲伏仔光先兆全共再列印合吉向后同名宇存安字守州帆年旭早有求百弛竹米羊羽臣自至舟行衣西回如成亨吾均坐壮声妙孝宏局希序志戒改更杏材村位佑作伯伴体余克兑兔兵初判利助告君步江汗汝池秀究良见言谷豆赤车辰并事亨京依佳侃供侍使佩来例免雨其具典冒冽函刻刷刹制到效协卓卷取受和周命固坤垂坦坡夜奇奈奉姑始妹枚板林欣武汲决沙汰冲沛沐沃汪炎炊版物牧玖的直盲知社空究舌虎采金长昔明旺服朋杭果枝松扭东门青季孟宜官宗宙定尚居岳岸岩岱幸庚店府弦征彼往快忽忠念或所房技承折扶政放齐于昂昆昌升昊亭亮系侠信俊保便侣俞冒冠克前则劲勉勃勇南厚叙咸哄品垠奎奏威姻姬姜妍姿客宣室屋巷帝幽度回建彦待律思性易招拓折拜抱施映昨是春星昭架柯查柴柔柘韦柱柏柄柳段油泳沿河况注泉泰治波泊法冷炳帅甚界皆皇盈看相眉祈科秋秒穿突竿红罕美耐肖衍表要计订贞军重门面革音风飞食首香姣乘倚幸仓修借值倍仿表伦党兼倡刚原员哥唐哲城夏娥宴家宫宰容射展峡岛峰师席库航般芽芹花芝芳娘袁衿活洪洲洗洞派洋流烘烈特珂珊珍玲益真词神祝组祚秦秤租秘并竟级纱纯素纳纽纺纹翁者耘耿育股皋座庭径徐恩恭恢恒恤息恬扇拾持效料旁旅晏晃时晋书朔校格桂根栖桃桐钊殊气记训讨豹贡财起马支高娟倩娜乾伟偕健偶侧停侦富凰剩副勘动务区卿参唯启商唱珠般产皎尽眼婉研祥移竟章笛伏笙弦紫绅绍绊累罩习翌者聊胡教敏斌斜旌旋族晤晨晚曹望朗梗梧梓梅梨梁毫球海浩涉涌浴烽爽崇国基坚执堂培寅寄宿寂密尉寻将专崔巢常带康强张彩彤雕彬从悦悟戚挺英婕若苔苗茂术袖许责赦近闭雪顷顶鹿麦麻佩闰闵雅雁集云项须顺劳喜乔善单喻围堪尧场堤报堡媒媚寒寓寻尊岚巽帽几复惟情荀茜茶扇掘卷扫舍掌迫贰量开闲间添焰无为犁猛球现理番媛登授捷敢散敦斑敲斯晶晴晰最替期朝棋棍栈森植荒草接棠栋棒棉款证注评象贵贴贸越超迪茹旋茫众街词渊涯涵混深淑清净浅淘淡焕发盛砚稀稍税程窗竣童策答筑筒等笔筏栗绞给吉绚絮绝统络翔能舜黄黑备传割胜仅债杰催惠伤舒倦传勤势募嗣园圆块干廊渡湃渺照煎媒炼爷琴琢琵琶棱督睦碇禁禄禽稚坚绢义圣廉巢微爱意惮荣感愚想愉愈斟新暑会极楚楠枫椰榆殿景钞钦温港渠湖湘测汤铁佃附雌雉聘肆琛唇脱台获莓莫蜀衙裟装裕里解咏夸詹资迹跳路载农退乃郊电赓雷颂顿预饮驯驰鼎鼓雍经莆莎莉普创诏雄博弼智贺皓凯团图境寿梦奖察实对伪侨像佟煌仆僚崭廓彰愿慈慎态搬业旗畅槐枪沟歌溢温溪源滋支溶熊雨犒猿狮瑚瑟瑞鼓监尽廖硕祯福种称竭端个算精紧绰绶综绯绵维纶绫置翠翡靖晖台与舞菊董华果菜蜻蜜裙裳诫诰诲诚誓说诞认貌赈宾暄铃轻赵群郎酸铅阁韶领饰饱仿魁魏诗试询诠援挥扬凰鸣鼻齐瑛瑗榕碌诱宁玮椿曾琳群杨虞当盟酩仪俭僻剧剑劈刘啸豪娇宽番寮履帜广熙弹影微彻慰慷醉锐锄锋阅院阵宵霆霈颐落蝶冲褓复课谈调谅论赐质赏卖趣践辈轮替游进邮部醇确磁磐稼稿谷稻穷箱节箭范篇糊纬缘缄绪线致缔编练暑义铺馆苇叶葛葵管萱著董慕虑掴摧摩数暂暴暮概乐槽樟枢标模样楼欢毅演汉渐涨滞漫满洋熟热荧瑶玛郎几皓盘驾驻魄鸦华燕惯慧嘉碧樊蒂颖块发葆渔漆纲尝彰志赫辅造逍速逞途透通逢连静萤阴银铜铭齐仅万冀剑进器喷坛壁奋道岭憬憧抚怜战撮撤撰幢播扑整德晕厉机陆陵鞘头余默龙桦横桥橇树樽橙竖洁润泄贤增郭赋烨烧燃炖磷燎芦穆窥筛筑糖县罢翰举苍蒸席辉震墨卫衡亲谓谒诚谏谚诸豫蹄辑办运远遇遂道达都醒钢锦铮锡钱总橡震敬慧磊庆儒优赏励壕壑岳应忆撼擒擎检操擅择擂敛檄檀褒讲谦谢豁趋融远乡键针钟锻阶队阳隆霜鞠韩馆骏鲜黛点齐鸿荫襁激浓营灿烛燧微响独瞰瞬禅簇篷纵繁缝声聪聊临艰参蔗谅蔬篷莲赛璜燃兴学遥晓霖澄潮潜潭蓉蓄茜颖璇蓓陶陈谘璋逸霓谋戴搁拟擦断曜曛曙归濠阔湿济涛爵获狞猎环瞻礼馈箫绣织缮翻异职旧荫蕊蕃蝉声讴谨丰转遭适鄙医锁镇锤镰聂鸡乡离雏额频骑鹃灿蕙鞭碧霞蒲劝宝庐扩攀旷莹泻溅瀑滨镜关雾韵愿类鲸鹊鹏麓兽猎祷稳获薄绳薪蔷薇襟识证赞赠辞郑鞠丰题简还释钟阐露飘馨璃龄宝迈怀悬胧沥献琼砾籍筹篮继办罗麒藏萨籍薯觉触译议警赢面锈瀚瀛烁蕾选辽遵迟臆臂膺荡画顾翻饶驱莺鹤鸡傈属巍续缠腊护誉贴轰辩随隐霸竞耀宝艺俨巅摄权欢灌叠穰笼听澡苏芦览赞读边鉴乡餐须蔺懿樱铁岩恋织藓兰变矿显驿验髓体乐麟龚矗罐艳禳酿炉陇谒灵鹰鑫听篱蛮观才湾瞩赞逻爵厌锣銮缆艳欢鹦麓';
		return $str;
	}
}//end class