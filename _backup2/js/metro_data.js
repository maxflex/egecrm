var metro_data = {
	"stations": {
		"1": {
			"name": "Бульвар Рокоссовского",
			"lineId": 1,
			"linkIds": [0],
			"labelId": 1,
			"isTransferStation": false,
			"insideCircle": false
		},
		"2": {
			"name": "Черкизовская",
			"lineId": 1,
			"linkIds": [0, 1],
			"labelId": 2,
			"isTransferStation": false,
			"insideCircle": false
		},
		"3": {
			"name": "Преображенская площадь",
			"lineId": 1,
			"linkIds": [1, 2],
			"labelId": 3,
			"isTransferStation": false,
			"insideCircle": false
		},
		"4": {
			"name": "Сокольники",
			"lineId": 1,
			"linkIds": [2, 3],
			"labelId": 4,
			"isTransferStation": false,
			"insideCircle": false
		},
		"5": {
			"name": "Красносельская",
			"lineId": 1,
			"linkIds": [3, 4],
			"labelId": 5,
			"isTransferStation": false,
			"insideCircle": false
		},
		"6": {
			"name": "Комсомольская",
			"lineId": 1,
			"linkIds": [4, 5, 6],
			"labelId": 6,
			"isTransferStation": true,
			"transferIds" : [77],
			"insideCircle": true
		},
		"7": {
			"name": "Красные ворота",
			"lineId": 1,
			"linkIds": [5, 7],
			"labelId": 7,
			"isTransferStation": false,
			"insideCircle": true
		},
		"8": {
			"name": "Чистые пруды",
			"lineId": 1,
			"linkIds": [7, 8, 9, 10],
			"labelId": 8,
			"isTransferStation": true,
			"transferIds" : [92, 159],
			"insideCircle": true
		},
		"9": {
			"name": "Лубянка",
			"lineId": 1,
			"linkIds": [8, 11, 12],
			"labelId": 9,
			"isTransferStation": true,
			"transferIds" : [117],
			"insideCircle": true
		},
		"10": {
			"name": "Охотный ряд",
			"lineId": 1,
			"linkIds": [11, 13, 14],
			"labelId": 10,
			"isTransferStation": true,
			"transferIds" : [29, 48],
			"insideCircle": true
		},
		"11": {
			"name": "Библиотека имени Ленина",
			"lineId": 1,
			"linkIds": [13, 15, 16, 17, 18],
			"labelId": 11,
			"isTransferStation": true,
			"transferIds" : [68, 144, 49],
			"insideCircle": true
		},
		"12": {
			"name": "Кропоткинская",
			"lineId": 1,
			"linkIds": [15, 19],
			"labelId": 12,
			"isTransferStation": false,
			"insideCircle": true
		},
		"13": {
			"name": "Парк культуры",
			"lineId": 1,
			"linkIds": [19, 20, 21],
			"labelId": 13,
			"isTransferStation": true,
			"transferIds" : [71],
			"insideCircle": true
		},
		"14": {
			"name": "Фрунзенская",
			"lineId": 1,
			"linkIds": [20, 22],
			"labelId": 14,
			"isTransferStation": false,
			"insideCircle": false
		},
		"15": {
			"name": "Спортивная",
			"lineId": 1,
			"linkIds": [22, 23],
			"labelId": 15,
			"isTransferStation": false,
			"insideCircle": false
		},
		"16": {
			"name": "Воробьевы горы",
			"lineId": 1,
			"linkIds": [23, 24],
			"labelId": 16,
			"isTransferStation": false,
			"insideCircle": false
		},
		"17": {
			"name": "Университет",
			"lineId": 1,
			"linkIds": [24, 25],
			"labelId": 17,
			"isTransferStation": false,
			"insideCircle": false
		},
		"18": {
			"name": "Проспект Вернадского",
			"lineId": 1,
			"linkIds": [25, 26],
			"labelId": 18,
			"isTransferStation": false,
			"insideCircle": false
		},
		"19": {
			"name": "Юго-Западная",
			"lineId": 1,
			"linkIds": [26],
			"labelId": 19,
			"isTransferStation": false,
			"insideCircle": false
		},
		"20": {
			"name": "Речной вокзал",
			"lineId": 2,
			"linkIds": [27],
			"labelId": 20,
			"isTransferStation": false,
			"insideCircle": false
		},
		"21": {
			"name": "Водный стадион",
			"lineId": 2,
			"linkIds": [27, 28],
			"labelId": 21,
			"isTransferStation": false,
			"insideCircle": false
		},
		"22": {
			"name": "Войковская",
			"lineId": 2,
			"linkIds": [28, 29],
			"labelId": 22,
			"isTransferStation": false,
			"insideCircle": false
		},
		"23": {
			"name": "Сокол",
			"lineId": 2,
			"linkIds": [29, 30],
			"labelId": 23,
			"isTransferStation": false,
			"insideCircle": false
		},
		"24": {
			"name": "Аэропорт",
			"lineId": 2,
			"linkIds": [30, 31],
			"labelId": 24,
			"isTransferStation": false,
			"insideCircle": false
		},
		"25": {
			"name": "Динамо",
			"lineId": 2,
			"linkIds": [31, 32],
			"labelId": 25,
			"isTransferStation": false,
			"insideCircle": false
		},
		"26": {
			"name": "Белорусская",
			"lineId": 2,
			"linkIds": [32, 33, 34],
			"labelId": 26,
			"isTransferStation": true,
			"transferIds" : [80],
			"insideCircle": true
		},
		"27": {
			"name": "Маяковская",
			"lineId": 2,
			"linkIds": [33, 35],
			"labelId": 27,
			"isTransferStation": false,
			"insideCircle": true
		},
		"28": {
			"name": "Тверская",
			"lineId": 2,
			"linkIds": [35, 36, 37, 38],
			"labelId": 28,
			"isTransferStation": true,
			"transferIds" : [116, 143],
			"insideCircle": true
		},
		"29": {
			"name": "Театральная",
			"lineId": 2,
			"linkIds": [14, 36, 39, 40],
			"labelId": 29,
			"isTransferStation": true,
			"transferIds" : [10, 48],
			"insideCircle": true
		},
		"30": {
			"name": "Новокузнецкая",
			"lineId": 2,
			"linkIds": [39, 41, 42, 43],
			"labelId": 30,
			"isTransferStation": true,
			"transferIds" : [94, 132],
			"insideCircle": true
		},
		"31": {
			"name": "Павелецкая",
			"lineId": 2,
			"linkIds": [41, 44, 45],
			"labelId": 31,
			"isTransferStation": true,
			"transferIds" : [74],
			"insideCircle": true
		},
		"32": {
			"name": "Автозаводская",
			"lineId": 2,
			"linkIds": [44, 46],
			"labelId": 32,
			"isTransferStation": false,
			"insideCircle": false
		},
		"33": {
			"name": "Коломенская",
			"lineId": 2,
			"linkIds": [46, 47],
			"labelId": 33,
			"isTransferStation": false,
			"insideCircle": false
		},
		"34": {
			"name": "Каширская",
			"lineId": 2,
			"linkIds": [47, 48, 49],
			"labelId": 34,
			"isTransferStation": true,
			"transferIds" : [170],
			"insideCircle": false
		},
		"35": {
			"name": "Кантемировская",
			"lineId": 2,
			"linkIds": [48, 50],
			"labelId": 35,
			"isTransferStation": false,
			"insideCircle": false
		},
		"36": {
			"name": "Царицыно",
			"lineId": 2,
			"linkIds": [50, 51],
			"labelId": 36,
			"isTransferStation": false,
			"insideCircle": false
		},
		"37": {
			"name": "Орехово",
			"lineId": 2,
			"linkIds": [51, 52],
			"labelId": 37,
			"isTransferStation": false,
			"insideCircle": false
		},
		"38": {
			"name": "Домодедовская",
			"lineId": 2,
			"linkIds": [52, 53],
			"labelId": 38,
			"isTransferStation": false,
			"insideCircle": false
		},
		"39": {
			"name": "Красногвардейская",
			"lineId": 2,
			"linkIds": [53, 54, 55],
			"labelId": 39,
			"isTransferStation": true,
			"transferIds" : [185],
			"insideCircle": false
		},
		"40": {
			"name": "Щелковская",
			"lineId": 3,
			"linkIds": [56],
			"labelId": 40,
			"isTransferStation": false,
			"insideCircle": false
		},
		"41": {
			"name": "Первомайская",
			"lineId": 3,
			"linkIds": [56, 57],
			"labelId": 41,
			"isTransferStation": false,
			"insideCircle": false
		},
		"42": {
			"name": "Измайловская",
			"lineId": 3,
			"linkIds": [57, 58],
			"labelId": 42,
			"isTransferStation": false,
			"insideCircle": false
		},
		"43": {
			"name": "Партизанская",
			"lineId": 3,
			"linkIds": [58, 59],
			"labelId": 43,
			"isTransferStation": false,
			"insideCircle": false
		},
		"44": {
			"name": "Семеновская",
			"lineId": 3,
			"linkIds": [59, 60],
			"labelId": 44,
			"isTransferStation": false,
			"insideCircle": false
		},
		"45": {
			"name": "Электрозаводская",
			"lineId": 3,
			"linkIds": [60, 61],
			"labelId": 45,
			"isTransferStation": false,
			"insideCircle": false
		},
		"46": {
			"name": "Бауманская",
			"lineId": 3,
			"linkIds": [61, 62],
			"labelId": 46,
			"isTransferStation": false,
			"insideCircle": false
		},
		"47": {
			"name": "Курская",
			"lineId": 3,
			"linkIds": [62, 63, 64, 65],
			"labelId": 47,
			"isTransferStation": true,
			"transferIds" : [76, 160],
			"insideCircle": true
		},
		"48": {
			"name": "Площадь Революции",
			"lineId": 3,
			"linkIds": [40, 63, 66],
			"labelId": 48,
			"isTransferStation": true,
			"transferIds" : [29, 10],
			"insideCircle": true
		},
		"49": {
			"name": "Арбатская",
			"lineId": 3,
			"linkIds": [16, 66, 67, 68, 69],
			"labelId": 49,
			"isTransferStation": true,
			"transferIds" : [68, 144, 11],
			"insideCircle": true
		},
		"50": {
			"name": "Смоленская",
			"lineId": 3,
			"linkIds": [67, 70],
			"labelId": 50,
			"isTransferStation": false,
			"insideCircle": true
		},
		"51": {
			"name": "Киевская",
			"lineId": 3,
			"linkIds": [70, 71, 72, 73],
			"labelId": 51,
			"isTransferStation": true,
			"transferIds" : [65, 82],
			"insideCircle": true
		},
		"52": {
			"name": "Парк Победы",
			"lineId": 3,
			"linkIds": [71, 74, 75],
			"labelId": 52,
			"isTransferStation": true,
			"transferIds" : [194],
			"insideCircle": false
		},
		"53": {
			"name": "Славянский бульвар",
			"lineId": 3,
			"linkIds": [74, 76],
			"labelId": 53,
			"isTransferStation": false,
			"insideCircle": false
		},
		"54": {
			"name": "Кунцевская",
			"lineId": 3,
			"linkIds": [76, 77, 78],
			"labelId": 54,
			"isTransferStation": true,
			"transferIds" : [58], 
			"insideCircle": false
		},
		"55": {
			"name": "Молодежная",
			"lineId": 3,
			"linkIds": [77, 79],
			"labelId": 55,
			"isTransferStation": false,
			"insideCircle": false
		},
		"56": {
			"name": "Крылатское",
			"lineId": 3,
			"linkIds": [79, 80],
			"labelId": 56,
			"isTransferStation": false,
			"insideCircle": false
		},
		"57": {
			"name": "Строгино",
			"lineId": 3,
			"linkIds": [80, 81],
			"labelId": 57,
			"isTransferStation": false,
			"insideCircle": false
		},
		"58": {
			"name": "Кунцевская",
			"lineId": 4,
			"linkIds": [78, 82],
			"labelId": 54,
			"isTransferStation": true,
			"transferIds" : [54],
			"insideCircle": false
		},
		"59": {
			"name": "Пионерская",
			"lineId": 4,
			"linkIds": [82, 83],
			"labelId": 59,
			"isTransferStation": false,
			"insideCircle": false
		},
		"60": {
			"name": "Филевский парк",
			"lineId": 4,
			"linkIds": [83, 84],
			"labelId": 60,
			"isTransferStation": false,
			"insideCircle": false
		},
		"61": {
			"name": "Багратионовская",
			"lineId": 4,
			"linkIds": [84, 85],
			"labelId": 61,
			"isTransferStation": false,
			"insideCircle": false
		},
		"62": {
			"name": "Фили",
			"lineId": 4,
			"linkIds": [85, 86],
			"labelId": 62,
			"isTransferStation": false,
			"insideCircle": false
		},
		"63": {
			"name": "Кутузовская",
			"lineId": 4,
			"linkIds": [86, 87],
			"labelId": 63,
			"isTransferStation": false,
			"insideCircle": false
		},
		"64": {
			"name": "Студенческая",
			"lineId": 4,
			"linkIds": [87, 88],
			"labelId": 64,
			"isTransferStation": false,
			"insideCircle": false
		},
		"65": {
			"name": "Киевская",
			"lineId": 4,
			"linkIds": [72, 88, 89, 90, 91],
			"labelId": 51,
			"isTransferStation": true,
			"transferIds" : [51, 82],
			"insideCircle": true
		},
		"66": {
			"name": "Смоленская",
			"lineId": 4,
			"linkIds": [89, 92],
			"labelId": 66,
			"isTransferStation": false,
			"insideCircle": true
		},
		"67": {
			"name": "Арбатская",
			"lineId": 4,
			"linkIds": [92, 93],
			"labelId": 67,
			"isTransferStation": false,
			"insideCircle": true
		},
		"68": {
			"name": "Александровский сад",
			"lineId": 4,
			"linkIds": [17, 68, 93],
			"labelId": 68,
			"isTransferStation": true,
			"transferIds" : [144, 49, 11],
			"insideCircle": true
		},
		"69": {
			"name": "Выставочная",
			"lineId": 4,
			"linkIds": [90, 94, 95],
			"labelId": 69,
			"isTransferStation": true,
			"transferIds": [193],
			"insideCircle": false
		},
		"70": {
			"name": "Международная",
			"lineId": 4,
			"linkIds": [94],
			"labelId": 70,
			"isTransferStation": false,
			"insideCircle": false
		},
		"71": {
			"name": "Парк культуры",
			"lineId": 5,
			"linkIds": [21, 96, 115],
			"labelId": 13,
			"isTransferStation": true,
			"transferIds" : [13],
			"insideCircle": true,
			"isCircle": true
		},
		"72": {
			"name": "Октябрьская",
			"lineId": 5,
			"linkIds": [96, 97, 98],
			"labelId": 72,
			"isTransferStation": true,
			"transferIds" : [95],
			"insideCircle": true,
			"isCircle": true
		},
		"73": {
			"name": "Добрынинская",
			"lineId": 5,
			"linkIds": [97, 99, 100],
			"labelId": 73,
			"isTransferStation": true,
			"transferIds" : [146],
			"insideCircle": true,
			"isCircle": true
		},
		"74": {
			"name": "Павелецкая",
			"lineId": 5,
			"linkIds": [45, 99, 101],
			"labelId": 31,
			"isTransferStation": true,
			"transferIds" : [31],
			"insideCircle": true,
			"isCircle": true
		},
		"75": {
			"name": "Таганская",
			"lineId": 5,
			"linkIds": [101, 102, 103, 104],
			"labelId": 75,
			"isTransferStation": true,
			"transferIds" : [119, 131],
			"insideCircle": true,
			"isCircle": true
		},
		"76": {
			"name": "Курская",
			"lineId": 5,
			"linkIds": [64, 102, 105, 106],
			"labelId": 47,
			"isTransferStation": true,
			"transferIds" : [47, 160],
			"insideCircle": true,
			"isCircle": true
		},
		"77": {
			"name": "Комсомольская",
			"lineId": 5,
			"linkIds": [6, 105, 107],
			"labelId": 6,
			"isTransferStation": true,
			"transferIds" : [6],
			"insideCircle": true,
			"isCircle": true
		},
		"78": {
			"name": "Проспект Мира",
			"lineId": 5,
			"linkIds": [107, 108, 109],
			"labelId": 78,
			"isTransferStation": true,
			"transferIds" : [90],
			"insideCircle": true,
			"isCircle": true
		},
		"79": {
			"name": "Новослободская",
			"lineId": 5,
			"linkIds": [108, 110, 111],
			"labelId": 79,
			"isTransferStation": true,
			"transferIds" : [141],
			"insideCircle": true,
			"isCircle": true
		},
		"80": {
			"name": "Белорусская",
			"lineId": 5,
			"linkIds": [34, 110, 112],
			"labelId": 26,
			"isTransferStation": true,
			"transferIds" : [26],
			"insideCircle": true,
			"isCircle": true
		},
		"81": {
			"name": "Краснопресненская",
			"lineId": 5,
			"linkIds": [112, 113, 114],
			"labelId": 81,
			"isTransferStation": true,
			"transferIds" : [115],
			"insideCircle": true,
			"isCircle": true
		},
		"82": {
			"name": "Киевская",
			"lineId": 5,
			"linkIds": [73, 91, 113, 115],
			"labelId": 51,
			"isTransferStation": true,
			"transferIds" : [51, 65],
			"insideCircle": true,
			"isCircle": true
		},
		"83": {
			"name": "Медведково",
			"lineId": 6,
			"linkIds": [116],
			"labelId": 83,
			"isTransferStation": false,
			"insideCircle": false
		},
		"84": {
			"name": "Бабушкинская",
			"lineId": 6,
			"linkIds": [116, 117],
			"labelId": 84,
			"isTransferStation": false,
			"insideCircle": false
		},
		"85": {
			"name": "Свиблово",
			"lineId": 6,
			"linkIds": [117, 118],
			"labelId": 85,
			"isTransferStation": false,
			"insideCircle": false
		},
		"86": {
			"name": "Ботанический сад",
			"lineId": 6,
			"linkIds": [118, 119],
			"labelId": 86,
			"isTransferStation": false,
			"insideCircle": false
		},
		"87": {
			"name": "ВДНХ",
			"lineId": 6,
			"linkIds": [119, 120],
			"labelId": 87,
			"isTransferStation": false,
			"insideCircle": false
		},
		"88": {
			"name": "Алексеевская",
			"lineId": 6,
			"linkIds": [120, 121],
			"labelId": 88,
			"isTransferStation": false,
			"insideCircle": false
		},
		"89": {
			"name": "Рижская",
			"lineId": 6,
			"linkIds": [121, 122],
			"labelId": 89,
			"isTransferStation": false,
			"insideCircle": false
		},
		"90": {
			"name": "Проспект Мира",
			"lineId": 6,
			"linkIds": [109, 122, 123],
			"labelId": 78,
			"isTransferStation": true,
			"transferIds" : [78],
			"insideCircle": true
		},
		"91": {
			"name": "Сухаревская",
			"lineId": 6,
			"linkIds": [123, 124],
			"labelId": 91,
			"isTransferStation": false,
			"insideCircle": true
		},
		"92": {
			"name": "Тургеневская",
			"lineId": 6,
			"linkIds": [9, 124, 125, 126],
			"labelId": 92,
			"isTransferStation": true,
			"transferIds" : [8, 159],
			"insideCircle": true
		},
		"93": {
			"name": "Китай-город",
			"lineId": 6,
			"linkIds": [125, 127, 128],
			"labelId": 93,
			"isTransferStation": true,
			"transferIds" : [118],
			"insideCircle": true
		},
		"94": {
			"name": "Третьяковская",
			"lineId": 6,
			"linkIds": [42, 127, 129, 130],
			"labelId": 94,
			"isTransferStation": true,
			"transferIds" : [132, 30],
			"insideCircle": true
		},
		"95": {
			"name": "Октябрьская",
			"lineId": 6,
			"linkIds": [98, 129, 131],
			"labelId": 72,
			"isTransferStation": true,
			"transferIds" : [72],
			"insideCircle": true
		},
		"96": {
			"name": "Шаболовская",
			"lineId": 6,
			"linkIds": [131, 132],
			"labelId": 96,
			"isTransferStation": false,
			"insideCircle": false
		},
		"97": {
			"name": "Ленинский проспект",
			"lineId": 6,
			"linkIds": [132, 133],
			"labelId": 97,
			"isTransferStation": false,
			"insideCircle": false
		},
		"98": {
			"name": "Академическая",
			"lineId": 6,
			"linkIds": [133, 134],
			"labelId": 98,
			"isTransferStation": false,
			"insideCircle": false
		},
		"99": {
			"name": "Профсоюзная",
			"lineId": 6,
			"linkIds": [134, 135],
			"labelId": 99,
			"isTransferStation": false,
			"insideCircle": false
		},
		"100": {
			"name": "Новые Черемушки",
			"lineId": 6,
			"linkIds": [135, 136],
			"labelId": 100,
			"isTransferStation": false,
			"insideCircle": false
		},
		"101": {
			"name": "Калужская",
			"lineId": 6,
			"linkIds": [136, 137],
			"labelId": 101,
			"isTransferStation": false,
			"insideCircle": false
		},
		"102": {
			"name": "Беляево",
			"lineId": 6,
			"linkIds": [137, 138],
			"labelId": 102,
			"isTransferStation": false,
			"insideCircle": false
		},
		"103": {
			"name": "Коньково",
			"lineId": 6,
			"linkIds": [138, 139],
			"labelId": 103,
			"isTransferStation": false,
			"insideCircle": false
		},
		"104": {
			"name": "Теплый Стан",
			"lineId": 6,
			"linkIds": [139, 140],
			"labelId": 104,
			"isTransferStation": false,
			"insideCircle": false
		},
		"105": {
			"name": "Ясенево",
			"lineId": 6,
			"linkIds": [140, 141],
			"labelId": 105,
			"isTransferStation": false,
			"insideCircle": false
		},
		"106": {
			"name": "Новоясеневская",
			"lineId": 6,
			"linkIds": [141, 142],
			"labelId": 106,
			"isTransferStation": true,
			"transferIds" : [191],
			"insideCircle": false
		},
		"107": {
			"name": "Планерная",
			"lineId": 7,
			"linkIds": [143],
			"labelId": 107,
			"isTransferStation": false,
			"insideCircle": false
		},
		"108": {
			"name": "Сходненская",
			"lineId": 7,
			"linkIds": [143, 144],
			"labelId": 108,
			"isTransferStation": false,
			"insideCircle": false
		},
		"109": {
			"name": "Тушинская",
			"lineId": 7,
			"linkIds": [144, 145],
			"labelId": 109,
			"isTransferStation": false,
			"insideCircle": false
		},
		"110": {
			"name": "Щукинская",
			"lineId": 7,
			"linkIds": [146, 147],
			"labelId": 110,
			"isTransferStation": false,
			"insideCircle": false
		},
		"111": {
			"name": "Октябрьское поле",
			"lineId": 7,
			"linkIds": [147, 148],
			"labelId": 111,
			"isTransferStation": false,
			"insideCircle": false
		},
		"112": {
			"name": "Полежаевская",
			"lineId": 7,
			"linkIds": [148, 149],
			"labelId": 112,
			"isTransferStation": false,
			"insideCircle": false
		},
		"113": {
			"name": "Беговая",
			"lineId": 7,
			"linkIds": [149, 150],
			"labelId": 113,
			"isTransferStation": false,
			"insideCircle": false
		},
		"114": {
			"name": "Улица 1905 года",
			"lineId": 7,
			"linkIds": [150, 151],
			"labelId": 114,
			"isTransferStation": false,
			"insideCircle": false
		},
		"115": {
			"name": "Баррикадная",
			"lineId": 7,
			"linkIds": [114, 151, 152],
			"labelId": 115,
			"isTransferStation": true,
			"transferIds" : [81],
			"insideCircle": true
		},
		"116": {
			"name": "Пушкинская",
			"lineId": 7,
			"linkIds": [37, 152, 153, 154],
			"labelId": 116,
			"isTransferStation": true,
			"transferIds" : [28, 143],
			"insideCircle": true
		},
		"117": {
			"name": "Кузнецкий мост",
			"lineId": 7,
			"linkIds": [12, 153, 155],
			"labelId": 117,
			"isTransferStation": true,
			"transferIds" : [9],
			"insideCircle": true
		},
		"118": {
			"name": "Китай-город",
			"lineId": 7,
			"linkIds": [128, 155, 156],
			"labelId": 93,
			"isTransferStation": true,
			"transferIds" : [93],
			"insideCircle": true
		},
		"119": {
			"name": "Таганская",
			"lineId": 7,
			"linkIds": [103, 156, 157, 158],
			"labelId": 75,
			"isTransferStation": true,
			"transferIds" : [75, 131],
			"insideCircle": true
		},
		"120": {
			"name": "Пролетарская",
			"lineId": 7,
			"linkIds": [157, 159, 160],
			"labelId": 120,
			"isTransferStation": true,
			"transferIds" : [162],
			"insideCircle": false
		},
		"121": {
			"name": "Волгоградский проспект",
			"lineId": 7,
			"linkIds": [159, 161],
			"labelId": 121,
			"isTransferStation": false,
			"insideCircle": false
		},
		"122": {
			"name": "Текстильщики",
			"lineId": 7,
			"linkIds": [161, 162],
			"labelId": 122,
			"isTransferStation": false,
			"insideCircle": false
		},
		"123": {
			"name": "Кузьминки",
			"lineId": 7,
			"linkIds": [162, 163],
			"labelId": 123,
			"isTransferStation": false,
			"insideCircle": false
		},
		"124": {
			"name": "Рязанский проспект",
			"lineId": 7,
			"linkIds": [163, 164],
			"labelId": 124,
			"isTransferStation": false,
			"insideCircle": false
		},
		"125": {
			"name": "Выхино",
			"lineId": 7,
			"linkIds": [164, 165],
			"labelId": 125,
			"isTransferStation": false,
			"insideCircle": false
		},
		"126": {
			"name": "Новогиреево",
			"lineId": 8,
			"linkIds": [166, 167],
			"labelId": 126,
			"isTransferStation": false,
			"insideCircle": false
		},
		"127": {
			"name": "Перово",
			"lineId": 8,
			"linkIds": [166, 168],
			"labelId": 127,
			"isTransferStation": false,
			"insideCircle": false
		},
		"128": {
			"name": "Шоссе Энтузиастов",
			"lineId": 8,
			"linkIds": [168, 169],
			"labelId": 128,
			"isTransferStation": false,
			"insideCircle": false
		},
		"129": {
			"name": "Авиамоторная",
			"lineId": 8,
			"linkIds": [169, 170],
			"labelId": 129,
			"isTransferStation": false,
			"insideCircle": false
		},
		"130": {
			"name": "Площадь Ильича",
			"lineId": 8,
			"linkIds": [170, 171, 172],
			"labelId": 130,
			"isTransferStation": true,
			"transferIds" : [161],
			"insideCircle": false
		},
		"131": {
			"name": "Марксистская",
			"lineId": 8,
			"linkIds": [104, 158, 171, 173],
			"labelId": 131,
			"isTransferStation": true,
			"transferIds" : [75, 119],
			"insideCircle": true
		},
		"132": {
			"name": "Третьяковская",
			"lineId": 8,
			"linkIds": [43, 130, 173],
			"labelId": 94,
			"isTransferStation": true,
			"transferIds" : [94, 30],
			"insideCircle": true
		},
		"133": {
			"name": "Алтуфьево",
			"lineId": 9,
			"linkIds": [174],
			"labelId": 133,
			"isTransferStation": false,
			"insideCircle": false
		},
		"134": {
			"name": "Бибирево",
			"lineId": 9,
			"linkIds": [174, 175],
			"labelId": 134,
			"isTransferStation": false,
			"insideCircle": false
		},
		"135": {
			"name": "Отрадное",
			"lineId": 9,
			"linkIds": [175, 176],
			"labelId": 135,
			"isTransferStation": false,
			"insideCircle": false
		},
		"136": {
			"name": "Владыкино",
			"lineId": 9,
			"linkIds": [176, 177],
			"labelId": 136,
			"isTransferStation": false,
			"insideCircle": false
		},
		"137": {
			"name": "Петровско-Разумовская",
			"lineId": 9,
			"linkIds": [177, 178],
			"labelId": 137,
			"isTransferStation": false,
			"insideCircle": false
		},
		"138": {
			"name": "Тимирязевская",
			"lineId": 9,
			"linkIds": [178, 179],
			"labelId": 138,
			"isTransferStation": false,
			"insideCircle": false
		},
		"139": {
			"name": "Дмитровская",
			"lineId": 9,
			"linkIds": [179, 180],
			"labelId": 139,
			"isTransferStation": false,
			"insideCircle": false
		},
		"140": {
			"name": "Савеловская",
			"lineId": 9,
			"linkIds": [180, 181],
			"labelId": 140,
			"isTransferStation": false,
			"insideCircle": false
		},
		"141": {
			"name": "Менделеевская",
			"lineId": 9,
			"linkIds": [111, 181, 182],
			"labelId": 141,
			"isTransferStation": true,
			"transferIds" : [79],
			"insideCircle": true
		},
		"142": {
			"name": "Цветной бульвар",
			"lineId": 9,
			"linkIds": [182, 183, 184],
			"labelId": 142,
			"isTransferStation": true,
			"transferIds" : [158],
			"insideCircle": true
		},
		"143": {
			"name": "Чеховская",
			"lineId": 9,
			"linkIds": [38, 154, 183, 185],
			"labelId": 143,
			"isTransferStation": true,
			"transferIds" : [28, 116],
			"insideCircle": true
		},
		"144": {
			"name": "Боровицкая",
			"lineId": 9,
			"linkIds": [18, 69, 185, 186],
			"labelId": 144,
			"isTransferStation": true,
			"transferIds" : [68, 49, 11],
			"insideCircle": true
		},
		"145": {
			"name": "Полянка",
			"lineId": 9,
			"linkIds": [186, 187],
			"labelId": 145,
			"isTransferStation": false,
			"insideCircle": true
		},
		"146": {
			"name": "Серпуховская",
			"lineId": 9,
			"linkIds": [100, 187, 188],
			"labelId": 146,
			"isTransferStation": true,
			"transferIds" : [73],
			"insideCircle": true
		},
		"147": {
			"name": "Тульская",
			"lineId": 9,
			"linkIds": [188, 189],
			"labelId": 147,
			"isTransferStation": false,
			"insideCircle": false
		},
		"148": {
			"name": "Нагатинская",
			"lineId": 9,
			"linkIds": [189, 190],
			"labelId": 148,
			"isTransferStation": false,
			"insideCircle": false
		},
		"149": {
			"name": "Нагорная",
			"lineId": 9,
			"linkIds": [190, 191],
			"labelId": 149,
			"isTransferStation": false,
			"insideCircle": false
		},
		"150": {
			"name": "Нахимовский проспект",
			"lineId": 9,
			"linkIds": [191, 192],
			"labelId": 150,
			"isTransferStation": false,
			"insideCircle": false
		},
		"151": {
			"name": "Севастопольская",
			"lineId": 9,
			"linkIds": [192, 193, 194],
			"labelId": 151,
			"isTransferStation": true,
			"transferIds" : [172],
			"insideCircle": false
		},
		"152": {
			"name": "Чертановская",
			"lineId": 9,
			"linkIds": [193, 195],
			"labelId": 152,
			"isTransferStation": false,
			"insideCircle": false
		},
		"153": {
			"name": "Южная",
			"lineId": 9,
			"linkIds": [195, 196],
			"labelId": 153,
			"isTransferStation": false,
			"insideCircle": false
		},
		"154": {
			"name": "Пражская",
			"lineId": 9,
			"linkIds": [196, 197],
			"labelId": 154,
			"isTransferStation": false,
			"insideCircle": false
		},
		"155": {
			"name": "Улица Академика Янгеля",
			"lineId": 9,
			"linkIds": [197, 198],
			"labelId": 155,
			"isTransferStation": false,
			"insideCircle": false
		},
		"156": {
			"name": "Аннино",
			"lineId": 9,
			"linkIds": [198, 199],
			"labelId": 156,
			"isTransferStation": false,
			"insideCircle": false
		},
		"157": {
			"name": "Бульвар Дмитрия Донского",
			"lineId": 9,
			"linkIds": [199, 200],
			"labelId": 157,
			"isTransferStation": true,
			"transferIds" : [173],
			"insideCircle": false
		},
		"158": {
			"name": "Трубная",
			"lineId": 10,
			"linkIds": [184, 201, 202],
			"labelId": 158,
			"isTransferStation": true,
			"transferIds" : [142],
			"insideCircle": true
		},
		"159": {
			"name": "Сретенский бульвар",
			"lineId": 10,
			"linkIds": [10, 126, 201, 203],
			"labelId": 159,
			"isTransferStation": true,
			"transferIds" : [92, 8],
			"insideCircle": true
		},
		"160": {
			"name": "Чкаловская",
			"lineId": 10,
			"linkIds": [65, 106, 203, 204],
			"labelId": 160,
			"isTransferStation": true,
			"transferIds" : [47, 76],
			"insideCircle": true
		},
		"161": {
			"name": "Римская",
			"lineId": 10,
			"linkIds": [172, 204, 205],
			"labelId": 161,
			"isTransferStation": true,
			"transferIds" : [130],
			"insideCircle": false
		},
		"162": {
			"name": "Крестьянская застава",
			"lineId": 10,
			"linkIds": [160, 205, 206],
			"labelId": 162,
			"isTransferStation": true,
			"transferIds" : [120],
			"insideCircle": false
		},
		"163": {
			"name": "Дубровка",
			"lineId": 10,
			"linkIds": [206, 207],
			"labelId": 163,
			"isTransferStation": false,
			"insideCircle": false
		},
		"164": {
			"name": "Кожуховская",
			"lineId": 10,
			"linkIds": [207, 208],
			"labelId": 164,
			"isTransferStation": false,
			"insideCircle": false
		},
		"165": {
			"name": "Печатники",
			"lineId": 10,
			"linkIds": [208, 209],
			"labelId": 165,
			"isTransferStation": false,
			"insideCircle": false
		},
		"166": {
			"name": "Волжская",
			"lineId": 10,
			"linkIds": [209, 210],
			"labelId": 166,
			"isTransferStation": false,
			"insideCircle": false
		},
		"167": {
			"name": "Люблино",
			"lineId": 10,
			"linkIds": [210, 211],
			"labelId": 167,
			"isTransferStation": false,
			"insideCircle": false
		},
		"168": {
			"name": "Братиславская",
			"lineId": 10,
			"linkIds": [211, 212],
			"labelId": 168,
			"isTransferStation": false,
			"insideCircle": false
		},
		"169": {
			"name": "Марьино",
			"lineId": 10,
			"linkIds": [212, 213],
			"labelId": 169,
			"isTransferStation": false,
			"insideCircle": false
		},
		"170": {
			"name": "Каширская",
			"lineId": 11,
			"linkIds": [49, 214],
			"labelId": 34,
			"isTransferStation": true,
			"transferIds" : [34],
			"insideCircle": false
		},
		"171": {
			"name": "Варшавская",
			"lineId": 11,
			"linkIds": [214, 215],
			"labelId": 171,
			"isTransferStation": false,
			"insideCircle": false
		},
		"172": {
			"name": "Каховская",
			"lineId": 11,
			"linkIds": [194, 215],
			"labelId": 172,
			"isTransferStation": true,
			"transferIds" : [151],
			"insideCircle": false
		},
		"173": {
			"name": "Улица Старокачаловская",
			"lineId": 12,
			"linkIds": [200, 216, 217],
			"labelId": 173,
			"isTransferStation": true,
			"transferIds" : [157],
			"insideCircle": false
		},
		"174": {
			"name": "Улица Скобелевская",
			"lineId": 12,
			"linkIds": [216, 218],
			"labelId": 174,
			"isTransferStation": false,
			"insideCircle": false
		},
		"175": {
			"name": "Бульвар Адмирала Ушакова",
			"lineId": 12,
			"linkIds": [218, 219],
			"labelId": 175,
			"isTransferStation": false,
			"insideCircle": false
		},
		"176": {
			"name": "Улица Горчакова",
			"lineId": 12,
			"linkIds": [219, 220],
			"labelId": 176,
			"isTransferStation": false,
			"insideCircle": false
		},
		"177": {
			"name": "Бунинская Аллея",
			"lineId": 12,
			"linkIds": [220],
			"labelId": 177,
			"isTransferStation": false,
			"insideCircle": false
		},
		"178": {
			"name": "Мякинино",
			"lineId": 3,
			"linkIds": [81, 221],
			"labelId": 178,
			"isTransferStation": false,
			"insideCircle": false
		},
		"179": {
			"name": "Волоколамская",
			"lineId": 3,
			"linkIds": [221, 222],
			"labelId": 179,
			"isTransferStation": false,
			"insideCircle": false
		},
		"180": {
			"name": "Митино",
			"lineId": 3,
			"linkIds": [222, 223],
			"labelId": 180,
			"isTransferStation": false,
			"insideCircle": false
		},
		"181": {
			"name": "Марьина Роща",
			"lineId": 10,
			"linkIds": [224],
			"labelId": 181,
			"isTransferStation": false,
			"insideCircle": false
		},
		"182": {
			"name": "Достоевская",
			"lineId": 10,
			"linkIds": [202, 224],
			"labelId": 182,
			"isTransferStation": false,
			"insideCircle": false
		},
		"183": {
			"name": "Борисово",
			"lineId": 10,
			"linkIds": [213, 225],
			"labelId": 183,
			"isTransferStation": false,
			"insideCircle": false
		},
		"184": {
			"name": "Шипиловская",
			"lineId": 10,
			"linkIds": [225, 226],
			"labelId": 184,
			"isTransferStation": false,
			"insideCircle": false
		},
		"185": {
			"name": "Зябликово",
			"lineId": 10,
			"linkIds": [54, 226],
			"labelId": 185,
			"isTransferStation": true,
			"transferIds" : [39],
			"insideCircle": false
		},
		"186": {
			"name": "Новокосино",
			"lineId": 8,
			"linkIds": [167],
			"labelId": 186,
			"isTransferStation": false,
			"insideCircle": false
		},
		"187": {
			"name": "Алма-Атинская",
			"lineId": 2,
			"linkIds": [55],
			"labelId": 187,
			"isTransferStation": false,
			"insideCircle": false
		},
		"188": {
			"name": "Пятницкое шоссе",
			"lineId": 3,
			"linkIds": [223],
			"labelId": 188,
			"isTransferStation": false,
			"insideCircle": false
		},
		"189": {
			"name": "Лермонтовский проспект",
			"lineId": 7,
			"linkIds": [165, 227],
			"labelId": 189,
			"isTransferStation": false,
			"insideCircle": false
		},
		"190": {
			"name": "Жулебино",
			"lineId": 7,
			"linkIds": [227],
			"labelId": 190,
			"isTransferStation": false,
			"insideCircle": false
		},
		"191": {
			"name": "Битцевский парк",
			"lineId": 12,
			"linkIds": [142, 228],
			"labelId": 191,
			"isTransferStation": true,
			"transferIds" : [106],
			"insideCircle": false
		},
		"192": {
			"name": "Лесопарковая",
			"lineId": 12,
			"linkIds": [217, 228],
			"labelId": 192,
			"isTransferStation": false,
			"insideCircle": false
		},
		"193": {
			"name": "Деловой центр",
			"lineId": 8,
			"linkIds": [95, 229],
			"labelId": 193,
			"isTransferStation": true,
			"transferIds": [69],
			"insideCircle": false
		},
		"194": {
			"name": "Парк Победы",
			"lineId": 8,
			"linkIds": [75, 229],
			"labelId": 52,
			"isTransferStation": true,
			"transferIds" : [52],
			"insideCircle": false
		},
		"195": {
			"name": "Спартак",
			"lineId": 7,
			"linkIds": [145, 146],
			"labelId": 195,
			"isTransferStation": false,
			"insideCircle": false
		},
		"196": {
			"name": "Тропарёво",
			"lineId": 1,
			"labelId": 196,
			"isTransferStation": false,
			"insideCircle": false
		}
	},
	"stationCount": 195,
	"lines": {
		"1": {
			"name": "Сокольническая линия",
			"color": "#EF1E25",
			"stationIds": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
			"linkIds": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26],
			"transferStationIds": [6, 8, 8, 9, 10, 11, 11, 11, 13]
		},
		"2": {
			"name": "Замоскворецкая линия",
			"color": "#029A55",
			"stationIds": [20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 187],
			"linkIds": [14, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55],
			"transferStationIds": [29, 26, 28, 28, 29, 30, 30, 31, 34, 39]
		},
		"3": {
			"name": "Арбатско-Покровская линия",
			"color": "#0252A2",
			"stationIds": [40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 178, 179, 180, 188],
			"linkIds": [16, 40, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 221, 222, 223],
			"transferStationIds": [49, 48, 47, 47, 49, 49, 51, 51, 52, 54]
		},
		"4": {
			"name": "Филевская линия",
			"color": "#019EE0",
			"stationIds": [58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70],
			"linkIds": [17, 68, 72, 78, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95],
			"transferStationIds": [68, 68, 65, 58, 65, 69]
		},
		"5": {
			"name": "Кольцевая линия",
			"color": "#745C2F",
			"stationIds": [71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82],
			"linkIds": [6, 21, 34, 45, 64, 73, 91, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115],
			"transferStationIds": [77, 71, 80, 74, 76, 82, 82, 72, 73, 75, 75, 76, 78, 79, 81]
		},
		"6": {
			"name": "Калужско-Рижская линия",
			"color": "#FBAA33",
			"stationIds": [83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106],
			"linkIds": [9, 42, 98, 109, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142],
			"transferStationIds": [92, 94, 95, 90, 92, 93, 94, 106]
		},
		"7": {
			"name": "Таганско-Краснопресненская линия",
			"color": "#B61D8E",
			"stationIds": [107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 189, 190, 195],
			"linkIds": [12, 37, 103, 114, 128, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 227],
			"transferStationIds": [117, 116, 119, 115, 118, 116, 119, 120]
		},
		"8": {
			"name": "Калининская линия",
			"color": "#FFD803",
			"stationIds": [126, 127, 128, 129, 130, 131, 132, 186, 193, 194],
			"linkIds": [43, 75, 95, 104, 130, 158, 166, 167, 168, 169, 170, 171, 172, 173, 229],
			"transferStationIds": [132, 194, 193, 131, 132, 131, 130]
		},
		"9": {
			"name": "Серпуховско-Тимирязевская линия",
			"color": "#ACADAF",
			"stationIds": [133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157],
			"linkIds": [18, 38, 69, 100, 111, 154, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200],
			"transferStationIds": [144, 143, 144, 146, 141, 143, 142, 151, 157]
		},
		"10": {
			"name": "Люблинско-Дмитровская линия",
			"color": "#B1D332",
			"stationIds": [158, 159, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 181, 182, 183, 184, 185],
			"linkIds": [10, 54, 65, 106, 126, 160, 172, 184, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 224, 225, 226],
			"transferStationIds": [159, 185, 160, 160, 159, 162, 161, 158]
		},
		"11": {
			"name": "Каховская линия",
			"color": "#5091BB",
			"stationIds": [170, 171, 172],
			"linkIds": [49, 194, 214, 215],
			"transferStationIds": [170, 172]
		},
		"12": {
			"name": "Бутовская линия",
			"color": "#85D4F3",
			"stationIds": [173, 174, 175, 176, 177, 191, 192],
			"linkIds": [142, 200, 216, 217, 218, 219, 220, 228],
			"transferStationIds": [191, 173]
		}
	}
}