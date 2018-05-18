// Jurchin Main Js file
// Author : jafar rezaei


var jurchin = {

	build : function() {

		// convert english digits to persian digits
		String.prototype.toFaDigit = function () {
			return this.replace(/\d+/g, function (digit) {
				var ret = "";
				var i;
				for (i = 0; i < digit.length; i += 1) {
					ret += String.fromCharCode(digit.charCodeAt(i) + 1728);
				}
				return ret;
			});
		};


		// convert persian digits to english digits
		String.prototype.toEnDigit = function () {
			return this.replace(/[\u06F0-\u06F9]+/g, function (digit) {
				var ret = "";
				var i;
				for (i = 0; i < digit.length; i += 1) {
					ret += String.fromCharCode(digit.charCodeAt(i) - 1728);
				}
				return ret;
			});
		};


		$(document).ready(function () {

			$("body").tooltip({
				selector: '[rel="tooltip"]'
			});


			// simple show another div functionality
			$('body').on("click" , ".showAnother" , function() {
				var nextShow = $(this).attr("data-show");
				if($("#"+nextShow).is(":visible")){
					$("#"+nextShow).fadeOut();
				}else{
					$("#"+nextShow).fadeIn();
				}
			})
			.on("keydown" , "input.isnumber" ,function(t) {
				-1 !== $.inArray(t.keyCode, [46, 8, 9, 27, 13, 110, 190]) || 65 == t.keyCode && t.ctrlKey === !0 || 67 == t.keyCode && t.ctrlKey === !0 || 88 == t.keyCode && t.ctrlKey === !0 || t.keyCode >= 35 && t.keyCode <= 39 || (t.shiftKey || t.keyCode < 48 || t.keyCode > 57) && (t.keyCode < 96 || t.keyCode > 105) && t.preventDefault()
			})
			.on("blur" , "input.isnumber" ,function(t) {
				$(this).prop("value",$(this).val().toEnDigit());
			})


			.on("blur", "input.hasPx" ,function(t) {
				if($(this).val().indexOf("px") == -1)
					$(this).val($(this).val() + "px");
			})

			.on("focus", "input.hasPx" , function(t) {
				if($(this).val().indexOf("px") == -1)
					$(this).val($(this).val());
				else
					$(this).val($(this).val().replace(/px/g , ""));

				// select text in input
				this.select();
			})

			.on("keypress", "input.ispersian", function(t) {
				-1 !== $.inArray(t.keyCode, [46, 8, 9, 27, 13]) || (65 == t.charCode || 97 == t.charCode) && t.ctrlKey === !0 || t.keyCode >= 35 && t.keyCode <= 39 || -1 == $.inArray(String.fromCharCode(t.charCode), ["â€Œ", " ", "Ø¢", "Ø§", "Ø¨", "Ù¾", "Øª", "Ø«", "Ø¬", "Ú†", "Ø­", "Ø®", "Ø¯", "Ø°", "Ø±", "Ø²", "Ú˜", "Ø³", "Ø´", "Øµ", "Ø¶", "Ø·", "Ø¸", "Ø¹", "Øº", "Ù", "Ù‚", "Ú©", "Ú¯", "Ù„", "Ù…", "Ù†", "Ùˆ", "Ù‡", "ÛŒ", "ÙŠ", "Ùƒ", "Ø©"]) && t.preventDefault()
			});

			$("[data-timeAgo]").each(function() {
				var date = $(this).attr("data-timeAgo");
				$(this).html(jurchin.msago(date));
			});


			// footer sntence
			var sentences = [
				"دوستتون داره",
				"عشق به وبسایت رو زیاد میکنه",
				"چیزی پیدا نمیکنه اینجا بنویسه",
				"طراحی رابط کاربر حرفه ای",
				"یه سایت ساز خفن واسه شماست",
				"میخاد که حالتون خوب باشه :)",
				"با Drag-Drop کار میکنه ...",
				"تمرکز اصلیش روی سادگیه",
				"موبایل و تبلت رو دوس داره",
				"سرعت بارگذاری سایت براش مهمه",
				"همیشه رضایت شما رو میخاد",
				"با خوبی شما هر روز بهتر میشه",
				"کسب و کارتون رو مکانیزه میکنه",
				"مناسب برای توسعه دهندگان وب",
				"هیچ لزومی به فهمیدن کدنویسی نداره",
				"یه تیم حرفه ای پشتیبانش هستن",
			];
			var choosed = jurchin.getRandom(sentences.length - 1);
			$("#jorchinSaid").html(sentences[choosed]);


			// main modal handler
			$("[data-toggle=modal]:not(.overModal)").attr("data-target" , "#JurchinMainModal");
			$("[data-toggle=modal].overModal").attr("data-target" , "#JurchinOverModal");


			// main ajax form handle
			$("form.jrAjax").each(function(e) {
				var a = $(this);
				var t = !1;
				$(this).off().on("submit", function(e) {
					$("button.close").click();
					if (!t) {
						var n = a.serialize();
						var route = a.attr("data-route");
						t = !0;
						$.ajax({
							type: a.attr("method"),
							data: n + "&d=" + Math.floor(9999999 * Math.random() + 1),
							url: a.attr("action") ,
							xhrFields: {
								withCredentials: true
							},
							beforeSend: function() {
								jurchin.showNotification('bottom','left','<i class="fa fa-cog fa-spin ml5" style="font-size:16px;margin-bottom:-2px;"></i>در حال اجرای درخواست ...', 'success' , 15000);
							},
							success: function(e) {
								t = !1;

								try{
									e = JSON.parse(e);
								}catch(error){
									console.log(e);
									console.log(error);
								}

								if(e.message == "ok"){
									$("button.close").click();
									jurchin.showNotification('bottom','left','عملیات با موفقیت انجام شد ...', 'info' , 1500 , "check");
									if(route.length > 0){
										window.location = siteurl+route;
									}
								}else{
									$("button.close").click();
									jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i> خطا ، پیام :‌ '+ e.message , 'danger' , 1500);
								}
							},
							error: function() {

								$("button.close").click();
								jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i> خطا در اجرا !', 'danger' , 1500);
							},
							timeout: 25000
						})
					}
					e.preventDefault()
				})
			});


		});

	},

	// random genarator
	getRandom : function (until) {
		return Math.floor((Math.random() * until) + 1);
	},

	validateEmail: function (email) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	},

	initUserMainPage: function() {
		jurchin.initDashboardPageCharts();

		$(document).ready(function () {

			$("#persianDate").html(jurchin.getPersianDate());

			window.setInterval(function(){
				$("#clock").html(jurchin.getTime());
			}, 1000);
		});

	},

	siteCheck: function (e) {

		if (window.event) {
			var charCode = window.event.keyCode;
		}else if (e) {
			var charCode = e.which;
		}else { return true; }

		if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) ||
				charCode == 8 ||		// allow delete
				charCode == 45 ||		// allow -
				(charCode > 47 && charCode < 58)
			)
			return true;
		else{
			return false;
		}

	},

	getTime: function () {

		d = new Date;
		nhour = d.getHours();
		nmin = d.getMinutes();
		function pad (n) { return ("0" + n).slice(-2); }
		return pad(nmin) + "<blink> : </blink>" + pad(nhour) ;

	},

	getPersianDate: function () {
		week = new Array("يكشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه", "شنبه")
		months = new Array("فروردين", "ارديبهشت", "خرداد", "تير", "مرداد", "شهريور", "مهر", "آبان", "آذر", "دي", "بهمن", "اسفند");
		a = new Date();
		d = a.getDay();
		day = a.getDate();
		month = a.getMonth() + 1;
		year = a.getYear();
		year = (year == 0) ? 2000 : year;
		(year < 1000) ? (year += 1900) : true;
		year -= ((month < 3) || ((month == 3) && (day < 21))) ? 622 : 621;
		switch (month) {
			case 1:
				(day < 21) ? (month = 10, day += 10) : (month = 11, day -= 20);
				break;
			case 2:
				(day < 20) ? (month = 11, day += 11) : (month = 12, day -= 19);
				break;
			case 3:
				(day < 21) ? (month = 12, day += 9) : (month = 1, day -= 20);
				break;
			case 4:
				(day < 21) ? (month = 1, day += 11) : (month = 2, day -= 20);
				break;
			case 5:
			case 6:
				(day < 22) ? (month -= 3, day += 10) : (month -= 2, day -= 21);
				break;
			case 7:
			case 8:
			case 9:
				(day < 23) ? (month -= 3, day += 9) : (month -= 2, day -= 22);
				break;
			case 10:
				(day < 23) ? (month = 7, day += 8) : (month = 8, day -= 22);
				break;
			case 11:
			case 12:
				(day < 22) ? (month -= 3, day += 9) : (month -= 2, day -= 21);
				break;
			default:
				break;
		}
		return week[d] + " " + day + " " + months[month - 1] + " " + year;
	},


	initTable: function() {

		!function () {
			"use strict";
			var JurchinTable = {

				initialized: false,
				setting: null ,
				persianNums: [],
				persianLetters : [],

				initialize: function () {
					if (this.initialized === false) {
						this.initialized = true;
						this.build();
						this.events();
					}
				},

				build: function () {

					this.setting = $(".datatable-jurchin").attr("data-jrSetting").split(",");
					$("#tableSettingMenu").html('<div class="timeline-centered"><div class="timeline-entry left-aligned"><div class="timeline-entry-inner"><div title="" rel="tooltip" class="timeline-icon"></div></div></div></div>');

					if(typeof $(".datatable-jurchin").attr("data-persianNum") !== "undefined")
						this.persianNums = $(".datatable-jurchin").attr("data-persianNum").split(",");


					if(typeof $(".datatable-jurchin").attr("data-persianLetter") !== "undefined")
						this.persianLetters = $(".datatable-jurchin").attr("data-persianLetter").split(",");



					// Create correct sorting types with farsi digits
					$.extend( $.fn.dataTableExt.oSort, {
						"farsi-num-pre": function (a) {
							var x = String(a).replace(/<[\s\S]*?>/g, "").toEnDigit();
							return parseFloat(x);
						},
						"farsi-num-asc": function (a, b) {
							return ((a < b) ? -1 : ((a > b) ? 1 : 0));
						},
						"farsi-num-desc": function (a, b) {
							return ((a < b) ? 1 : ((a > b) ? -1 : 0));
						}
					});

					// Create correct sorting types with farsi string
					$.extend( $.fn.dataTableExt.oSort, {
						"farsi-string-pre": function (a) {
							return GetUniCode(a.toLowerCase());
						},
						"farsi-string-asc": function (a, b) {
							return ((a < b) ? -1 : ((a > b) ? 1 : 0));
						},
						"farsi-string-desc": function (a, b) {
							return ((a < b) ? 1 : ((a > b) ? -1 : 0));
						}
					});

					if($.inArray("count" , this.setting) !== -1){
						$("#tableSettingMenu > .timeline-centered").prepend(''+
							'<article class="timeline-entry left-aligned">'+
								'<div class="timeline-entry-inner">'+
									'<div title="" rel="tooltip" class="timeline-icon"></div>'+
									'<div class="timeline-label">'+
										'<label >'+$(".jrCount").text()+' :</label>'+
										'<input type="hidden" id="countRangeSlider" name="countRangeSlider" value="" />'+
										'<input type="hidden" id="maxCount" value="" />'+
										'<input type="hidden" id="minCount" value="" />'+
									'</div>'+
								'</div>'+
							'</article>');

						$.fn.dataTableExt.search.push(
							function( settings, data, dataIndex ) {
								var min = parseInt( $('#minCount').val(), 10 );
								var max = parseInt( $('#maxCount').val(), 10 );

								var countIndex = $(".datatable-jurchin").find("th.jrCount").index();
								var buys = parseFloat( data[countIndex].toEnDigit() ) || 0; // use data for the count column

								if ( ( isNaN( min ) && isNaN( max ) ) ||
									 ( isNaN( min ) && buys <= max ) ||
									 ( min <= buys	&& isNaN( max ) ) ||
									 ( min <= buys	&& buys <= max ) )
								{
									return true;
								}
								return false;
							}
						);
					}

					if($.inArray("date" , this.setting) !== -1){

						$("#tableSettingMenu > .timeline-centered").prepend(''+
							'<article class="timeline-entry left-aligned">'+
								'<div class="timeline-entry-inner">'+
									'<div title="" rel="tooltip" class="timeline-icon"></div>'+
									'<div class="timeline-label">'+
										'<label >'+$(".jrDate").text()+' :</label>'+
										'<input type="hidden" id="dateRangeSlider" name="dateRangeSlider" value="" />'+
										'<input type="hidden" id="maxDate" value="" />'+
										'<input type="hidden" id="minDate" value="" />'+
									'</div>'+
								'</div>'+
							'</article>');
						$.fn.dataTableExt.search.push(
							function( settings, data, dataIndex ) {
								var min = parseInt($('#minDate').val(), 10);
								var max = parseInt($('#maxDate').val(), 10);

								var dateIndex = $(".datatable-jurchin").find("th.jrDate").index();
								var dateInTable = parseInt( data[dateIndex].toEnDigit().replace(/\//g, ""), 10) || 0; // use data for the date column

								if ( ( isNaN( min ) && isNaN( max ) ) ||
									 ( isNaN( min ) && dateInTable <= max ) ||
									 ( min <= dateInTable	&& isNaN( max ) ) ||
									 ( min <= dateInTable	&& dateInTable <= max ) )
								{
									return true;
								}
								return false;
							}
						);
					}

					if($.inArray("letter" , this.setting)  !== -1 ){
						$("#tableSettingMenu > .timeline-centered").prepend(''+
							'<article class="timeline-entry left-aligned">'+
								'<div class="timeline-entry-inner">'+
									'<div title="" rel="tooltip" class="timeline-icon"></div>'+
									'<div class="timeline-label">'+
										'<label >'+$(".jrLetter").text()+' در بازه :</label>'+
										'<input type="hidden" id="letterRangeSlider" name="letterRangeSlider" value="" />'+
										'<input type="hidden" id="maxLetter" value="" />'+
										'<input type="hidden" id="minLetter" value="" />'+
									'</div>'+
								'</div>'+
							'</article>');

						$.fn.dataTableExt.search.push(
							function( settings, data, dataIndex ) {
								var min = GetUniCode($('#minLetter').val()).slice(-2);
								var max = GetUniCode($('#maxLetter').val()).slice(-2);


								var letterIndex = $(".datatable-jurchin").find("th.jrLetter").index();
								var firstChar = data[letterIndex].replace(/[^A-Za-zآ-ی]/g,'')[0];
								var dateInTable = parseInt(GetUniCode( firstChar.toLowerCase()).slice(-3) || 0); // use data for the username column

								if(isEnglish(firstChar)){
									dateInTable = dateInTable-96;
								}


								if ( min <= dateInTable	&& dateInTable <= max ) {
									return true;
								}
								return false;
							}
						);
					}

				},

				events: function () {

					$(document).ready(function () {

						var selectedRowsCount = 0;
						// reset timeline values
						$("#minCount , #maxCount , #minDate , #maxDate").prop("value" , "");
						$("#maxLetter").prop("value" , "ی");
						$("#minLetter").prop("value" , "آ");

						JurchinTable.Table = $(".datatable-jurchin").DataTable({
							"searching": true,
							"ordering": true,
							"paginate": true,
							"pageLength": 10,
							"order": [[0, "asc"]],
							"fnDrawCallback": function () {
								$(".dataTables_filter").hide();
							},
							"columnDefs": [
								{type: "farsi-num", targets: JurchinTable.persianNums},
								{type: "farsi-string", targets: JurchinTable.persianLetters}
							]
						});

						var t = JurchinTable.Table;


						// add count slider
						if($.inArray("count" , JurchinTable.setting)  !== -1 ){
							// Get columns maximum and minimum values
							var minCount = t.column('.jrCount').data().sort()[0].toString().toEnDigit() - 1;
							var maxCount = t.column('.jrCount').data().sort().reverse()[0].toString().toEnDigit();

							if(!minCount || minCount == -1)
								minCount= 0;
							if(!maxCount || maxCount == 0)
								maxCount= 5;

							// Count Slider
							$("#countRangeSlider").ionRangeSlider({
								type: "double",
								min: minCount,
								max: maxCount,
								keyboard: true,
								onFinish: function (data) {
									var value = $("#countRangeSlider").prop("value");
									var minMaxCount = value.toString().split(";");
									$('#maxCount').val(maxCount - minMaxCount[0] + parseInt(minCount));
									$('#minCount').val(maxCount - minMaxCount[1] + parseInt(minCount));

									var countIndex = $(".datatable-jurchin").find("th.jrCount").index();
									var sortKind = JurchinTable.getSortingKind(countIndex);
									t.order([countIndex, sortKind]).draw();
								},
								prettify: function (num) {
									if(Number.isInteger(num)){
										num = (maxCount - num) + parseInt(minCount);
										return num;
									}
								}
							});
						}

						// add date slider
						if($.inArray("date" , JurchinTable.setting)  !== -1 ){
							var minDate = t.column('.jrDate').data().sort()[0].toString().toEnDigit();
							var maxDate = t.column('.jrDate').data().sort().reverse()[0].toString().toEnDigit();

							var minDateUnix = moment(minDate, 'jYYYY/jMM/jDD').format("X").toEnDigit();
							var maxDateUnix = moment(maxDate, 'jYYYY/jMM/jDD').format("X").toEnDigit();


							if(minDateUnix == "Invalid date"){
								minDateUnix= (Math.floor(Date.now() / 1000) - (24*3600*7)).toString();
							}
							if(maxDateUnix == "Invalid date"){
								maxDateUnix= Math.floor(Date.now() / 1000).toString();
							}

							// Date Slider
							$("#dateRangeSlider").ionRangeSlider({
								type: "double",
								min: minDateUnix,
								max: maxDateUnix,
								grid: true,
								force_edges: true,
								onFinish: function (data) {
									var value = $("#dateRangeSlider").prop("value");
									var minMaxDate = value.toString().split(";");

									var farsiMin = (parseInt(maxDateUnix.toEnDigit()) - minMaxDate[0])+ parseInt(minDateUnix.toEnDigit());
									var farsiMax = (parseInt(maxDateUnix.toEnDigit()) - minMaxDate[1])+ parseInt(minDateUnix.toEnDigit());

									var correctTimePersianMin = moment(farsiMin, "X").locale("fa").format("jYYYYjMMjDD");
									var correctTimePersianMax = moment(farsiMax, "X").locale("fa").format("jYYYYjMMjDD");

									$('#maxDate').val(correctTimePersianMin.toEnDigit());
									$('#minDate').val(correctTimePersianMax.toEnDigit());

									var dateIndex = $(".datatable-jurchin").find("th.jrDate").index();
									var sortKind = JurchinTable.getSortingKind(dateIndex);
									t.order([dateIndex, sortKind]).draw();
								},
								prettify: function (num) {
									num = (maxDateUnix - num) + parseInt(minDateUnix);
									var m = moment(num, "X").locale("fa");
									return m.format("jYY/jM/jD");
								}
							});
						}

						// add letter slider
						if($.inArray("letter" , JurchinTable.setting)  !== -1 ){
							$("#letterRangeSlider").ionRangeSlider({
								type: "double",
								min: 0,
								max: 32,
								grid: true,
								grid_num:10,
								onFinish: function (data) {
									var value = $("#letterRangeSlider").prop("value");
									var minMaxLetter = value.toString().split(";");
									$('#maxLetter').val(a[32-minMaxLetter[0]]);
									$('#minLetter').val(a[32-minMaxLetter[1]]);

									var letIndex = $(".datatable-jurchin").find("th.jrLetter").index();
									var sortKind = JurchinTable.getSortingKind(letIndex);
									t.order([letIndex , sortKind]).draw();
								},
								prettify: function (num) {
									return a[32-num];
								}
							});
						}

						// Extra Search field in sidebar
						$("#myInputTextField").bind("keyup", function () {
							t.search($(this).val()).draw();
						});


						// Make better paging with bs btn-group
						$(".dataTables_paginate").addClass("btn-group");

					});

				},

				getSortingKind: function(n){
					// Get current sort kind to keep
					var el = $(".datatable-jurchin > thead th:nth-child("+(n+1)+")");
					if(el.hasClass("sorting_desc")){
						return "desc";
					}else{
						return "asc";
					}
				}
			};

			JurchinTable.initialize();

		}();

	},

	initImageHandle: function(maxFileSize = 1) {
		$('body').on("change", ".file_Upload", function() {
			var file = this.files[0];
			var imagefile = file.type;
			var match = ["image/jpeg", "image/png", "image/jpg"];
			if (!((imagefile == match[0]) || (imagefile == match[1]) || (imagefile == match[2]))) {
				jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i> لطفا فایل با پسوند مجاز انتخاب کنید <br/>فایل های فرمت jpeg, jpg و png مجاز به آپلود هستند .', 'danger' , 1500);
				return false;
			} else if (this.files.length && this.files[0].size > 1000 * 1024 * maxFileSize) {
				jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i> حجم فایل انتخابی بیشتر از حجم مجاز است [' + jurchin.format_size(this.files[0].size) + '] <br/>حداکثر حجم مجاز برای بارگذاری ۵ مگابایت است .', 'danger' , 2000);
				return false;
			} else {
				var reader = new FileReader();
				reader.onload = function(e) {
					img = new Image();
					img.src = reader.result;
					img.onload = function() {
						if (this.width < 100 && this.height < 100) {
							jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i>طول و عرض فایل بسیار کوچک است . [' + this.width + 'px / ' + this.height + ' px]<br/>نباید کمتر از 100 پیکسل باشد .', 'danger' , 2000);
							return false;
						}
					};
					$("#previewAX").html('<img src='+e.target.result+' style="width:100%;height:auto;" />');
				};
				reader.readAsDataURL(this.files[0]);
			}
		});

	},

	initPostHandle: function(){
		jurchin.initImageHandle();

		!function () {
			"use strict";
			var JurchinPost = {

				initialized: false,
				setting: null ,

				initialize: function () {
					if (this.initialized === false) {
						this.initialized = true;
						this.build();
						this.events();
					}
				},

				build: function () {

					var index = 0;
					var letUpload = 1;

					// Function to preview image after validation
					$(document).on("change", ".file_Upload", function() {

						var file = this.files[0];
						var imagefile = file.type;
						var match = ["image/jpeg", "image/png", "image/jpg"];
						if (!((imagefile == match[0]) || (imagefile == match[1]) || (imagefile == match[2]))) {
							$("#previewAX").html("<img src='http://citygramcdn.ir/bphoto/dXNyVXBsb2FkL3RleHQyLnBuZzo6Ojo/220-220/upload.ctg' />");

							jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i> لطفا فایل با پسوند مجاز انتخاب کنید <br/>فایل های فرمت jpeg, jpg و png مجاز به آپلود هستند .', 'danger' , 1500);
							letUpload = 0;
							return false;
						} else if (this.files.length && this.files[0].size > 1000 * 1024) {
							$("#previewAX").html("<img src='http://citygramcdn.ir/bphoto/dXNyVXBsb2FkL3RleHQyLnBuZzo6Ojo/220-220/upload.ctg' />");
							jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i> حجم فایل انتخابی بیشتر از حجم مجاز است [' + JurchinPost.format_size(this.files[0].size) + '] <br/>حداکثر حجم مجاز برای آواتار اعضا ۵ مگابایت است .', 'danger' , 1500);
							letUpload = 0;
							return false;
						} else {
							var reader = new FileReader();
							reader.onload = function(e) {
								img = new Image();
								img.src = reader.result;
								img.onload = function() {
									if (this.width < 100 && this.height < 100) {
										$("#previewAX").html("<img src='http://citygramcdn.ir/bphoto/dXNyVXBsb2FkL3RleHQyLnBuZzo6Ojo/220/upload.ctg' />");
										jurchin.showNotification('bottom','left','<i class="fa fa-warn ml5" style="font-size:16px;margin-bottom:-2px;"></i>طول و عرض فایل بسیار کوچک است . [' + this.width + 'px / ' + this.height + ' px]<br/>نباید کمتر از 100 پیکسل باشد .', 'danger' , 1500);

										letUpload = 0;
										return false;
									}
								};
								$("#file").css("color", "green");
								$("#image_preview").css("display", "block");
								$("#previewing").attr("src", e.target.result);
								$("#previewing").attr("width", "auto");
								$("#previewing").attr("height", "220px");
							};
							reader.readAsDataURL(this.files[0]);
						}
					});



				},

				events: function () {

					$(document).ready(function () {

						// ----+++++ tag managment part +++++----- //

						$("#tagsInput").prop("value" , "");
						// add tag past
						$("#addTagPost").on("click",function () {
							var newTag = $("#tagsAdder").val().trim(", ،");

							if(newTag.length > 0){
								var newtagHtml = '<span class="tagsPost">'+
									'<span>'+
										'<i class="fa fa-close"></i>'+
									'</span>'+newTag+
								'</span>';

								$(".taglist").append(newtagHtml);
								$("#tagsInput").val($("#tagsInput").val()+newTag+",");
							}

							// delete value after add
							$("#tagsAdder").prop("value", "");
						});

						$("#tagsAdder").on("keypress" , function (e) {
							if ( e.which == 13 ){
								$("#addTagPost").click();
								return false;
							}else if(e.which == 32 || e.which == 188 || e.which == 1548 ){
								$("#addTagPost").click();
								return false;
							}
						})


						//delete tag
						$("body").on("click",".tagsPost>span" , function () {
							var tagVal = $(this).parent().text();
							$(this).parent().fadeOut(300,function(){
								$(this).remove();
								var newTagsInput = $("#tagsInput").val().replace(tagVal+"," , "");
								$("#tagsInput").prop("value",newTagsInput);
							});
						});

					});

				},


				format_size: function (size) {
					var units = ("B KB MB GB TB PB").split(" ");
					var mod = 1024;
					var i = 0;
					for (i = 0; size > mod; i++) {
						size /= mod;
					}
					size = size.toString();
					var endIndex = size.indexOf(".") + 3;
					return size.substr(0, endIndex) + " " + units[i];
				}

			};

			JurchinPost.initialize();

		}();

	},

	format_size: function (size) {
		var units = ("B KB MB GB TB PB").split(" ");
		var mod = 1024;
		var i = 0;
		for (i = 0; size > mod; i++) {
			size /= mod;
		}
		size = size.toString();
		var endIndex = size.indexOf(".") + 3;
		return size.substr(0, endIndex) + " " + units[i];
	},

	initPostsList: function() {

		$(document).on("click", "[data-toggle=modal]" , function(){

			var action = $(this).attr("data-action");
			var postTitle = $(this).parent().siblings(".title").text();
			var id = $(this).parent().closest("tr").attr("data-id");

			$form = $("div.modal").find("form");
			$form.attr("data-route" , "Dashboard/Post");
			$form.find("[name=action]").remove();
			$form.find("[name=paramID]").remove();
			$form.prepend("<input type='hidden' name='action' value='"+action+"' />");
			$form.prepend("<input type='hidden' name='paramID' value='"+id+"' />");

			if(action == "delete"){
				$("#JurchinModalTitle").html("<i class='fa fa-trash ml10'></i> حذف نوشته");
				$("#JurchinModalContent").html("آیا نوشته <span class='green-text'>"+postTitle+"</span> حذف شود ؟‌ <br/>");
			}

		});

		if(typeof parameter.delete !== "undefined"){
			$("tr[data-id="+parameter.delete+"]").find("[data-action=delete]").click();
		}
	},


	initSiteSetting: function() {
		$(document).ready(function () {

			// uploader image
			jurchin.initImageHandle();

			$("input[name='dateFormatChoose']").off().on("change", function() {
				$("input[name='dateFormat']").val($(this).val());
			});
			$("input[name='timeFormatChoose']").off().on("change", function() {
				$("input[name='timeformat']").val($(this).val());
			});
		});
	},

	initAccountEdit: function() {
		$(document).ready(function () {
			//http://www.jurchin.com/Webservice/Rest/getCities/country:107
			$("#user_country").on("change" , function () {
				var val = $(this).find("option:selected").val();
				$.get("http://www.jurchin.com/Webservice/Rest/getCities/country:" + val , function (e) {
					if(e.status){
						var options = "";
						for(var i=0;i< e.data.length;i++){
							var data = e.data[i];
							options += 	"<option value='"+data.id+"'>"+data.local_name+"</option>";
						}

						$("#user_city").html(options);
					}
				});
			});
		});
	},

	initCommentsList: function() {

		$(document).on("click", "[data-toggle=modal]" , function(){

			var action = $(this).attr("data-action");
			var dataComment = $(this).parents().eq(2).find(".comment").attr("data-comment");
			var usrnameFirstChar = $(this).parents().eq(2).find(".username").text()[0];
			var id = $(this).parent().closest("tr").attr("data-id");

			$form = $("div.modal").find("form");
			$form.attr("data-route" , "Dashboard/Comments");
			$form.find("[name=action]").remove();
			$form.find("[name=paramID]").remove();
			$form.prepend("<input type='hidden' name='action' value='"+action+"' />");
			$form.prepend("<input type='hidden' name='paramID' value='"+id+"' />");


			if(action == "delete"){
				$("#JurchinModalTitle").html("<i class='fa fa-trash ml10'></i> حذف نظر");
				$("#JurchinModalContent").html("آیا نظر زیر حذف شود ؟‌ <br/>"+$(this).parent().siblings(".comment").attr("data-comment"));
			}else if(action == "edit"){
				$("#JurchinModalTitle").html("<i class='fa fa-pencil ml10'></i>ویرایش نظر");
				$("#JurchinModalContent").html("<textarea type='text' name='commentContent' class='beutifulInput'>"+dataComment+"</textarea>");

				window.setTimeout(function () {$(".beutifulInput").focus().trigger("focus");},500);
			}else if(action == "show"){
				var answers = $(this).parent().closest("tr").attr("data-answers");

				try{
					answers = JSON.parse(answers);
				}catch(error){
					answers = new Object();
				}

				var answersShow = "";
				for(ans in answers){
					var thisAns = answers[ans][0];
					answersShow += "<div style='border-top:1px dotted #f4f4f4;margin-top:20px;padding-top:5px'><span class='avatarConv ml10' style='background:#eee;'>s</span>"+thisAns.answer+"<span class='pull-left ssmall' data-timeago='"+thisAns.date+"'></span></div>";
				}

				$("#JurchinModalTitle").html("<i class='fa fa-comments ml10'></i>مشاهده نظر");
				$("#JurchinModalContent").html("<div class='avatarConv'>"+usrnameFirstChar+"</div><div class='conversation' >"+dataComment+"</div><div>"+answersShow+"</div><div style='background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAAHCAYAAADNufepAAAAEklEQVQImWPYtGnTfwbCgAxlAMi+CUB7sDE0AAAAAElFTkSuQmCC) repeat-x;height:12px;width: 100%;margin: 25px auto;'></div>ارسال پاسخ:<br/><textarea type='text' name='commentContent' class='beutifulInput' required></textarea>");

				$("[data-timeAgo]").each(function() {
					var date = $(this).attr("data-timeAgo");
					$(this).html(jurchin.msago(date));
				});

			}

		});


		if(typeof parameter.show !== "undefined"){
			$("tr[data-id="+parameter.show+"]").find("[data-action=show]").eq(1).click();
		}

		if(typeof parameter.edit !== "undefined"){
			$("tr[data-id="+parameter.edit+"]").find("[data-action=edit]").click();
		}

		if(typeof parameter.delete !== "undefined"){
			$("tr[data-id="+parameter.delete+"]").find("[data-action=delete]").click();
		}
	},


	initSupport: function() {

		$(document).on("click", "[data-toggle=modal]" , function(){

			var action = $(this).attr("data-action");
			var ticketTitle = $(this).parent().siblings(".title").text();
			var id = $(this).parent().closest("[data-id]").attr("data-id");

			$form = $("div.modal").find("form");
			$form.attr("data-route" , "Dashboard/Support");
			$form.find("[name=action]").remove();
			$form.find("[name=paramID]").remove();
			$form.prepend("<input type='hidden' name='action' value='"+action+"' />");
			$form.prepend("<input type='hidden' name='paramID' value='"+id+"' />");

			if(action == "delete"){
				$("#JurchinModalTitle").html("<i class='fa fa-trash ml10'></i> حذف تیکت");
				$("#JurchinModalContent").html("آیا تیکت <span class='green-text'>"+ticketTitle+"</span> حذف شود ؟‌ <br/>");
			}else if(action == "close"){
				$("#JurchinModalTitle").html("<i class='fa fa-close ml10'></i>بستن تیکت");
				$("#JurchinModalContent").html("آیا تیکت <span class='green-text'>"+ticketTitle+"</span> بسته شود ؟‌ <br/>");
			}

		});

		if(typeof parameter.close !== "undefined"){
			$("tr[data-id="+parameter.close+"]").find("[data-action=close]").click();
		}

		if(typeof parameter.delete !== "undefined"){
			$("tr[data-id="+parameter.delete+"]").find("[data-action=delete]").click();
		}
	},


	initPages: function() {

		$(document).on("click", "[data-toggle=modal]" , function(){

			var action = $(this).attr("data-action");
			var id = $(this).parent().closest("tr").attr("data-id");

			$form = $("div.modal").find("form");
			$form.attr("data-route" , "Dashboard/Pages");
			$form.find("[name=action]").remove();
			$form.find("[name=paramID]").remove();
			$form.prepend("<input type='hidden' name='action' value='"+action+"' />");
			$form.prepend("<input type='hidden' name='paramID' value='"+id+"' />");

			var pageTitle = $(this).parent().siblings(".title").text().trim();

			if(action == "delete"){
				//index can not delete
				if(id == 'index'){
					window.location = siteurl+"Dashboard/Pages";
				}
				$("#JurchinModalTitle").html("<i class='fa fa-trash ml10'></i> حذف صفحه");
				$("#JurchinModalContent").html("آیا صفحه <span class='green-text'>"+pageTitle+"</span> حذف شود ؟‌ <br/>"+$(this).parent().siblings(".address").text());
			}else if(action == "edit"){
				var pageData = $(this).parent().closest("tr").attr("data-info");

				try{
					pageData = JSON.parse(pageData);
				}catch(error){
					pageData = new Object();
				}

				$("#JurchinModalTitle").html("<i class='fa fa-file ml10'></i> ویرایش صفحه");
				$("#JurchinModalContent").html(""+
					"<input type='text' name='pageTitle' class='beutifulInput' value='"+pageData.title+"' placeholder='عنوان صفحه' />"+
					"<input type='text' name='pageDes' class='beutifulInput mt5' value='"+pageData.description+"' placeholder='توضیح صفحه' />"+
					"<input type='text' name='pageKeyword' class='beutifulInput mt5' value='"+pageData.keywords+"' placeholder='کلماه کلیدی ۱-کلمه کلیدی ۲' />"+
					"<input type='hidden' name='pageImage' value='"+pageData.image+"' class='beutifulInput'/>"+
					"<input type='hidden' name='temp' value='index1' value='"+pageData.tempname+"' class='beutifulInput'/>"+
					"<div class='center  s16px' style='direction:ltr'><span>"+usersite+"/</span><input type='text' name='pageAddress' placeholder='آدرس صفحه' class='beutifulInput mt15 left' value='"+pageData.address+"' style='width:200px' /></div>");

				$("[name=pageDes]").on("keypress",function(t) {
					$("[name=pageKeyword]").val(t.target.value.replace(/ +/g, "-"));
				});
				window.setTimeout(function () {$(".beutifulInput[name=pageTitle]").focus().trigger("focus");},500);

			}else if(action == "new"){

				$("#JurchinModalTitle").html("<i class='fa fa-file ml10'></i> صفحه جدید");
				$("#JurchinModalContent").html("<div class='alert alert-info alert-with-icon' data-notify='container'>"+
						"<button type='button' aria-hidden='true' class='close' data-dismiss='alert' aria-label='close'>×</button>"+
						"<i data-notify='icon' class='material-icons'>add_alert</i>"+
						"<span data-notify='message'>بعد از فشار دادن دکمه تایید صفحه ساخته شده و وارد بخش ویرایش ظاهر شوید</span>"+
					"</div>"+
					"<input type='text' name='pageTitle' class='beutifulInput' placeholder='عنوان صفحه' />"+
					"<input type='text' name='pageDes' class='beutifulInput mt5' placeholder='توضیح صفحه' />"+
					"<input type='text' name='pageKeyword' class='beutifulInput mt5' placeholder='کلماه کلیدی ۱-کلمه کلیدی ۲' />"+
					"<input type='hidden' name='pageImage' class='beutifulInput'/>"+
					"<input type='hidden' name='temp' value='index1' class='beutifulInput'/>"+
					"<div class='center  s16px' style='direction:ltr'><span>"+usersite+"/</span><input type='text' name='pageAddress' placeholder='آدرس صفحه' class='beutifulInput mt15 left' style='width:200px' /></div>");

				$("[name=pageDes]").on("keypress",function(t) {
					$("[name=pageKeyword]").val(t.target.value.replace(/ +/g, "-"));
				});
				window.setTimeout(function () {$(".beutifulInput[name=pageTitle]").focus().trigger("focus");},500);
			}
		});


		if(typeof parameter.edit !== "undefined"){
			$("tr[data-id="+parameter.edit+"]").find("[data-action=edit]").click();
		}
		if(typeof parameter.delete !== "undefined"){
			$("tr[data-id="+parameter.delete+"]").find("[data-action=delete]").click();
		}
		if(typeof parameter.new !== "undefined"){
			$("[data-action='new']").click();
		}

	},


	initSiteUpgrade: function() {
		$(document).on("click", "[data-toggle=modal]" , function(){

			var action = $(this).attr("data-action");
			if(action == "confirm"){
				$("#JurchinModalTitle").html("<i class='fa fa-rocket ml10'></i>ارتقا سایت به نسخه پیشرفته");
				$("#JurchinModalContent").html("آیا از ارتقا سایت خود به نسخه پیشرفته و پرداخت <span class='green-text'>"+$(".UpgradePrice").text()+"</span> مطمئن هستید ؟ <br/>"+$(this).parent().siblings(".address").text());
			}

		});
	},


	initCatList: function() {

		$(document).on("click", "[data-toggle=modal]" , function(){

			var action = $(this).attr("data-action");
			var id = $(this).parent().closest("tr").attr("data-id");

			$form = $("div.modal").find("form");
			$form.attr("data-route" , "Dashboard/Categories");
			$form.find("[name=action]").remove();
			$form.find("[name=paramID]").remove();
			$form.prepend("<input type='hidden' name='action' value='"+action+"' />");
			$form.prepend("<input type='hidden' name='paramID' value='"+id+"' />");


			// if over modal
			var selector = "#JurchinModal";
			if($(this).attr("date-over")){
				selector += "Over";
			}

			var catTitle = $(this).parents().closest("tr").find(".catTitle").text().replace(/\[.*\]/, '').trim();
			if(action == "delete"){
				$("input[name=deletePosts]").prop("checked" , false);
				$(selector+"Title").html("<i class='fa fa-trash ml10'></i> حذف دسته");
				$(selector+"Content").html("آیا دسته بندی <span class='green-text'>"+catTitle+"</span> حذف شود ؟‌ <br/>با این دستور تمام زیر دسته های این دسته نیز حذف شده و پست های موجود در آنها به دسته عمومی منتقل میشوند ...<br/><br/><label><input type='checkbox' name='deletePosts' value='1' /> پست ها رو هم حذف کن ، انتقال لازم نیست </label>");
			}else if(action == "edit"){
				$(selector+"Title").html("<i class='fa fa-pencil ml10'></i>ویرایش دسته");
				$(selector+"Content").html("<input type='text' id='editCatInput' name='catTitle' class='beutifulInput' value='"+catTitle+"' />");

				window.setTimeout(function () {$("#editCatInput").focus().trigger("focus");},500);

			}else if(action == "subcats"){

				var subcats = $(this).parents().closest("tr").attr("data-subcats");
				var result = "";
				if(subcats.length > 0){
					subcats = subcats.slice(0, -1).split("-");
					if(subcats.length > 0){
						for (var i = subcats.length - 1; i >= 0; i--) {
							subcatData = subcats[i].split(",");
							result += ''+
							'<tr data-id="'+subcatData[0]+'">'+
								'<td style="width:60%" class="catTitle">'+subcatData[1]+'</td>'+
								'<td style="width:10%">'+subcatData[2]+'</td>'+
								'<td>'+
									'<a href="'+siteurl+'Dashboard/Post/List/cat:'+subcatData[0]+'" target="_blank" rel="tooltip" title="مشاهده پست ها" class="btn btn-round btn-success btn-fab btn-fab-mini">'+
										'<i class="material-icons">layers</i>'+
									'</a> '+

									'<span rel="tooltip" title="ویرایش" class="btn btn-round btn-primary btn-fab btn-fab-mini overModal" data-toggle="modal" date-over="over" data-target="#JurchinOverModal" data-action="edit" >'+
										'<i class="material-icons" >edit</i>'+
									'</span> '+

									'<span rel="tooltip" title="حذف" class="btn btn-round btn-danger btn-fab btn-fab-mini overModal" data-toggle="modal" date-over="over" data-target="#JurchinOverModal" data-action="delete">'+
										'<i class="material-icons" >delete</i>'+
									'</span>'+
								'</td>'+
							'</tr>';
						}
					}
				}else{
					result += ''+
					'<tr>'+
						'<td style="width:60%">بدون زیر دسته</td>'+
						'<td style="width:10%"></td>'+
						'<td></td>'+
					'</tr>';
				}


				$("#JurchinModalTitle").html("<i class='fa fa-folder-open ml10'></i> زیر دسته های "+catTitle);
				$("#JurchinModalContent").html('<table class="datatable-jurchin table table-bordered byekan dataTable no-footer">'+
						'<tbody>'+
							result+
						'</tbody>'+
					'</table>'
				);
			}else if(action == "new"){
				cats = JSON.parse(cats);
				var options = "<option value='0'>بدون والد</option>";
				for(c in cats){
					options += "<option value='"+c+"'>"+cats[c]+"</option>";
				}

				$(selector+"Title").html("<i class='fa fa-cube ml10'></i>دسته جدید");
				$(selector+"Content").html("<input type='text' placeholder='عنوان' name='catName' class='beutifulInput' value='' />"+
					"<div class='mt15 s16px'>"+
						"دسته والد :"+
						"<select name='parentCat' class='beutifulInput'>"+options+"</select>"+
					"</div>"
				);

				window.setTimeout(function () {$(".beutifulInput[name=catName]").focus().trigger("focus");},1000);
			}


		});

		$("tr[data-subcats]").each(function () {
			var subcats = $(this).attr("data-subcats");
			if(subcats.length > 0){
				subcats = subcats.slice(0, -1).split("-");
				var result = "";
				if(subcats.length > 0){
					var extraDots = "" ;
					if(subcats.length > 3){
						var MaxShowSub = 3 ;
						extraDots = " - ...";
					}else{
						var MaxShowSub = subcats.length - 1 ;
					}
					for (var i = MaxShowSub; i >= 0; i--) {
						subcatData = subcats[i].split(",");
						result += subcatData[1] + " - ";
					}
					$(this).find("td.catTitle").append("<span class='ssmall' style='margin-right:5px'>["+result.slice(0, -3)+extraDots+"]</span>");
				}
			}
		});


		$(window).on("load" , function(){

			if(typeof parameter.new !== "undefined") {
				$("[data-action='new']").click();
			}
			if(typeof parameter.edit !== "undefined") {
				$("tr[data-id="+parameter.edit+"]").find("[data-action='edit']").click();
			}
			if(typeof parameter.delete !== "undefined") {
				$("tr[data-id='"+parameter.delete+"']").find("[data-action='delete']").click();
			}
			if(typeof parameter.subcats !== "undefined") {
				$("tr[data-id='"+parameter.subcats+"']").find("[data-action='subcats']").click();
			}

		});


	},


	initSitesList: function() {

		$(document).on("click", "[data-toggle=modal]" , function(){

			var action = $(this).attr("data-action");
			var id = $(this).parent().closest("tr").attr("data-id");

			$form = $("div.modal").find("form");
			$form.attr("data-route" , "Dashboard/Sites");
			$form.find("[name=action]").remove();
			$form.find("[name=paramID]").remove();
			$form.prepend("<input type='hidden' name='action' value='"+action+"' />");
			$form.prepend("<input type='hidden' name='paramID' value='"+id+"' />");

			if(action == "delete"){
				$("#JurchinModalTitle").html("<i class='fa fa-trash ml10'></i> حذف سایت");
				$("#JurchinModalContent").html("آیا میخواهید وبسایت <span class='green-text'>"+$(this).parent().siblings(".title").text()+"</span> را خذف کنید ؟‌ <br/> این عمل قابل برگشت نمی باشد");
			}else if(action == "downloadTemplate"){
				$("#JurchinModalTitle").html("<i class='fa fa-desktop ml10'></i>دریافت قالب سایت");
				$("#JurchinModalContent").html("این امکان فعلا برای وبسایت شما محیا نشده است .");

			}

		});

	},

	initNewSite: function() {

		$(document).ready(function () {


			$('.nav-tabs li').click(function() {
				$(this).nextAll().removeClass('Greened');
			});

			// show part after change radio
			$('input[name="randomPwd"]').on("change" , function() {
				var value = $(this).val();
				if(value == "ok"){
					$("#addPasswordPart").fadeOut();
				}else{
					$("#addPasswordPart").fadeIn();
				}
			});


			//Wizard
			$('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
				var target = $(e.target);
				if (target.parent().hasClass('disabled')) {
					return false;
				}
			});

			var accountCheck = false;


			$("input[name='siteMail']").on("blur" , function() {

				if(userKey == ''){

					$.ajax({
						type: "get",
						dataType: 'json',
						url: siteurl + "Webservice/Rest/hasAccount/mail:" + $(this).val() + "-d:" + Math.floor(9999999 * Math.random() + 1),
						beforeSend: function() {
							jurchin.showNotification('bottom','left'," در حال بررسی حساب کاربری ...", 'success');
						},
						success: function(e) {

							if(e.status == false){
								$("button.close").click();
								accountCheck = true;
								jurchin.showNotification('bottom','left',"تبریک ، ایمیل توسط هیچ حسابی استفاده نمیشود .", 'success');
							}else{
								$("button.close").click();
								accountCheck = false;
								jurchin.showNotification('bottom','left',"این ایمیل قبلا توسط اکانت دیگری استفاده شده است ، اگر متعلق به شماست با وارد کردن رمز عبور به حساب خود وارد شوید.", "danger");
							}

						},
						error: function(e) {
							accountCheck = false;
						},
						timeout: 15000
					});
				}else{
					accountCheck = true;
				}

			});


			$('.next-step').click(function(e) {
				if ($(this).hasClass('firstTab')) {
					var kindSelected = $('input[name=kind]:checked').val();
					if (kindSelected !== 'self' && kindSelected !== 'content' && kindSelected !== 'comunity') {
						jurchin.showNotification('bottom','left',"لطفا یک گزینه انتخاب کنید ...", 'danger');
						return false;
					}
				}

				if ($(this).hasClass('secoundTab')) {
					if ($("#resCheck").attr("data-allowed") == "false") {

						jurchin.showNotification('bottom','left',"دامنه وارد شده معتبر نمی باشد .", 'danger');
						return false;
					}
					if ($("#resCheck").attr("data-allowed") == "notProccess") {

						jurchin.showNotification('bottom','left',"دامنه وارد شده بررسی نشده است.", 'danger');
						return false;
					}
				}


				if ($(this).hasClass('doneStep')) {

					if ($("[name=siteMail]").val().length == 0 || $("[name=siteTitle]").val().length == 0 || $("[name=firstName]").val().length == 0 || $("[name=lastName]").val().length == 0 ) {

						jurchin.showNotification('bottom','left',"اطلاعات سایت تکمیل نشده است .", 'danger');
						return false;
					}

					if($("[name=password]").val().length == 0 && $("input[name='randomPwd']:checked").val() == "no" ){
						jurchin.showNotification('bottom','left',"لطفا رمز عبوری برای خود تعریف کنید .", 'danger');
						return false;
					}

					if(!jurchin.validateEmail($("[name=siteMail]").val())){
						jurchin.showNotification('bottom','left'," ایمیل وارد شده معتبر نمی باشد .", 'danger');
						return false;
					}

				}


				var active = $('.wizard .nav-tabs li.active');
				active.addClass('Greened');
				active.next().removeClass('disabled').addClass('Greened');;
				nextTab(active);
			});

			$('.prev-step').click(function(e) {
				var active = $('.wizard .nav-tabs li.active');
				active.removeClass('Greened');
				prevTab(active);
			});



			$("#checkedSiteUrl").on("keypress" , function () {
				$("#resCheck").attr("data-allowed" , "notProccess").html("بررسی").removeClass("btn-success").addClass("btn-danger");
			});

			$("body").on("click" , "#resCheck" , function () {
				var i = "#resCheck";
				var address = $("#checkedSiteUrl").val();
				if(address.length < 4){
					jurchin.showNotification('bottom','left',"حداقل ۴ کاراکتر وارد کنید ." , 'danger');
					return false;
				}
				//console.log(siteurl + "Webservice/Rest/Checksite/address:" + address + "-d:" + Math.floor(9999999 * Math.random() + 1));
				$.ajax({
					type: "get",
					dataType: 'json',
					url: siteurl + "Webservice/Rest/Checksite/address:" + address + "-d:" + Math.floor(9999999 * Math.random() + 1),
					beforeSend: function() {
						$(i).fadeIn(200), $(i).html('<i class="fa fa-cog fa-spin ml5" style="font-size:16px;margin-bottom:-2px;"></i> کمی صبر کنید...')
					},
					success: function(e) {
						if(e.available == true)
							$(i).html("<i class='fa fa-check ml10'></i> آزاد است ").removeClass("btn-danger").addClass("btn-success").attr("data-allowed","true");
						else
							$(i).html("<i class='fa fa-close ml10'></i> مجاز نیست ").removeClass("btn-success").addClass("btn-danger").attr("data-allowed","false");
					},
					error: function(e) {

						console.log(e);
						$(i).html('<i class="fa fa-warning-sign ml5" style="font-size:16px;"></i> خطا !')
					},
					timeout: 15000
				});
			});
		});
		function nextTab(elem) {
			$(elem).next().find('a[data-toggle="tab"]').click();
		}

		function prevTab(elem) {
			$(elem).prev().find('a[data-toggle="tab"]').click();
		}

	},


	msago: function (ms) {
		function suffix (number) {
			return ((number > 1) ? ' ' : '') + ' پیش';
		}

		data = new Date().getTime();
		var temp = Math.ceil(data/1000)- ms ;

		var years = Math.floor(temp / 31536000);
		if (years)
			return years + ' سال' + suffix(years);

		var month = Math.floor((temp %= 31536000) / 2592000);
		if (month)
			return month + ' ماه' + suffix(month);

		var weeks = Math.floor((temp %= 2592000) / 604800);
		if (weeks)
			return weeks + ' ماه' + suffix(weeks);

		var days = Math.floor((temp %= 604800) / 86400);
		if (days)
			return days + ' روز' + suffix(days);

		var hours = Math.floor((temp %= 86400) / 3600);
		if (hours)
			return hours + ' ساعت' + suffix(hours);

		var minutes = Math.floor((temp %= 3600) / 60);
		if (minutes)
			return minutes + ' دقیقه' + suffix(minutes);

		var seconds = Math.floor(temp % 60);
		if (seconds)
			return seconds + ' ثانیه' + suffix(seconds);

		return 'همین حالا';
	},

	initDashboardPageCharts: function(){


		// _______Visits Chart

		var visitsChartSelector = '#vistsTableList';
		var visitsData = $(visitsChartSelector).attr("data-datas").slice(0, -1).split(",");
		var visitsLabel = $(visitsChartSelector).attr("data-labels").slice(0, -1).split(",");

		console.log(visitsData);
		var max = Math.max.apply(null,visitsData);
		max += Math.ceil(max/5);
		dataDailyVisitsChart = {
			labels: visitsLabel,
			series: [
				visitsData
			],
		};

		optionsDailyVisitsChart = {
			lineSmooth: Chartist.Interpolation.cardinal({
				tension: 0
			}),
			low: 0,
			high: max ,
			chartPadding: { top: 0, right: 0, bottom: 0, left: 0},
		}

		var responsiveOptions = [
		  ['screen and (max-width: 640px)', {
			seriesBarDistance: 5,
			axisX: {
			  labelInterpolationFnc: function (value) {
				return value[0];
			  }
			}
		  }]
		];

		var dailyVisitsChart = new Chartist.Line(visitsChartSelector, dataDailyVisitsChart, optionsDailyVisitsChart ,responsiveOptions);
		md.startAnimationForLineChart(dailyVisitsChart);




		// _______Comments Chart

		var commentsChartSelector = '#commentsTableList';
		var commentsData = $(commentsChartSelector).attr("data-datas").slice(0, -1).split(",");
		var commentsLabel = $(commentsChartSelector).attr("data-labels").slice(0, -1).split(",");

		var max = Math.max.apply(null,commentsData);
		max += Math.ceil(max/5);

		var dataEmailsSubscriptionChart = {
		  labels: commentsLabel ,
		  series: [
			commentsData
		  ]
		};
		var optionsEmailsSubscriptionChart = {
			axisX: {
				showGrid: false
			},
			low: 0,
			high: max,
			chartPadding: { top: 0, right: 5, bottom: 0, left: 0}
		};

		var emailsSubscriptionChart = Chartist.Bar(commentsChartSelector, dataEmailsSubscriptionChart, optionsEmailsSubscriptionChart, responsiveOptions);
		md.startAnimationForBarChart(emailsSubscriptionChart);

	},


	showNotification: function(from, align , message , kind="success",timer = 1500,icon="notifications"){
		$.notify({
			icon: icon ,
			message: message

		},{
			type: kind,
			timer: timer,
			placement: {
				from: from,
				align: align
			}
		});
	},

};

// build jurchin system
jurchin.build();
