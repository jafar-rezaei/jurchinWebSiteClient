<?php
$lang = array();

function getLang(){
	// login & registration classes
	$lang["MESSAGE_CAPTCHA_WRONG"] =  "کد امنیتی نادرست است !"; 
	$lang["MESSAGE_COOKIE_INVALID"] =  "کوکی نامعتبر است"; 
	$lang["MESSAGE_DATABASE_ERROR"] =  "خطا در اتصال به دیتابیس."; 
	$lang["MESSAGE_EMAIL_ALREADY_EXISTS"] =  "کاربری با آدرس ایمیل مشابه قبلا در سیستم ثبت نام کرده است. اگر رمز عبور خود را فراموش کرده اید ، به سادگی با کلیک روی دکمه 'رمزمو فراموش کردم' رمز عبور خودتون رو ریست کنید."; 
	$lang["MESSAGE_EMAIL_CHANGE_FAILED"] =  "متاسفیم ، تغییر آدرس ایمیل انجام نشد ."; 
	$lang["MESSAGE_EMAIL_CHANGED_SUCCESSFULLY"] =  "آدرس ایمیل شما با موفقیت تغییر داده شد. آدرس جدید ایمیل شما : "; 
	$lang["MESSAGE_EMAIL_EMPTY"] =  "ایمیل نمی تواند خالی باشد"; 
	$lang["MESSAGE_EMAIL_INVALID"] =  "آدرس ایمیل شما معتبر نمی باشد"; 
	$lang["MESSAGE_EMAIL_SAME_LIKE_OLD_ONE"] =  "متاسفیم ، آدرس ایمیل وارد شده همان آدرس ایمیل فعلی شماست . لطفا یک ایمیل دیگر وارد کنید."; 
	$lang["MESSAGE_EMAIL_TOO_LONG"] =  "آدرس ایمیل نمی تواند از 64 کارامتر بیشتر باشد"; 
	$lang["MESSAGE_LINK_PARAMETER_EMPTY"] =  "پارامتر لینک خالی می باشد."; 
	$lang["MESSAGE_LOGGED_OUT"] =  "شما با موفقیت خارج شدید."; 
	// The "login failed"-message is a security improved feedback that doesn't show a potential attacker if the user exists or not
	$lang["MESSAGE_LOGIN_FAILED"] =  "ورود انجام نشد !"; 
	$lang["MESSAGE_OLD_PASSWORD_WRONG"] =  "رمز عبور قبلی شما  اشتباه است."; 
	$lang["MESSAGE_PASSWORD_BAD_CONFIRM"] =  "رمز عبور و تکرار آن با هم برابر نیستند"; 
	$lang["MESSAGE_PASSWORD_CHANGE_FAILED"] =  "متاسفیم ، تغییر رمز عبور شما انجام نشد."; 
	$lang["MESSAGE_PASSWORD_CHANGED_SUCCESSFULLY"] =  "رمز عبور با موفقیت تغییر داده شد!"; 
	$lang["MESSAGE_PASSWORD_EMPTY"] =  "فیلد رمز عبور خالی می باشد"; 
	$lang["MESSAGE_PASSWORD_RESET_MAIL_FAILED"] =  "ایمیل ریست رمز عبور ارسال نشد ! خطا : "; 
	$lang["MESSAGE_PASSWORD_RESET_MAIL_SUCCESSFULLY_SENT"] =  "ایمیل ریست رمز عبور با موفقیت ارسال شد!"; 
	$lang["MESSAGE_PASSWORD_TOO_SHORT"] =  "رمز عبور حداقل باید 6 کاراکتر باشد"; 
	$lang["MESSAGE_PASSWORD_WRONG"] =  "رمز عبور اشتباه است . لطفا مجددا تلاش کنید."; 
	$lang["MESSAGE_PASSWORD_WRONG_3_TIMES"] =  "شما سه بار رمز عبور اشتباه را وارد کرده اید . برای ورود مجدد لطفا 60 ثانیه صبر کنید."; 
	$lang["MESSAGE_REGISTRATION_ACTIVATION_NOT_SUCCESSFUL"] =  "متاسفیم ، شناسه کاربری/کدفعالسازی شما معتبر نمی باشد ..."; 
	$lang["MESSAGE_REGISTRATION_ACTIVATION_SUCCESSFUL"] =  "حساب شما فعال شد ! هم اکنون وارد سایت شده اید . در حال انتقال ... <span class='ssmall'>[<a href='http://citygram.ir'>انتقال سریع</a>]</span>"; 
	$lang["MESSAGE_REGISTRATION_FAILED"] =  "متاسفیم ، ثبت نام شما انجام نشد ، لطفا مجددا تلاش کنید ."; 
	$lang["MESSAGE_RESET_LINK_HAS_EXPIRED"] =  "لینک ریست رمز عبور شما منقضی شده است . لطفا لینک ریست رمز عبور را در عرض 1 ساعت استفاده نمایید ."; 
	$lang["MESSAGE_VERIFICATION_MAIL_ERROR"] =  "متاسفیم ، ایمیل فعالسازی حساب برای شما ارسال نشد . ثبت نام شما انجام نشد ."; 
	$lang["MESSAGE_VERIFICATION_MAIL_NOT_SENT"] =  "ایمیل فعالسازی حساب ارسال نشد ! خطا : "; 
	$lang["MESSAGE_VERIFICATION_AGAIN_MAIL_SENT"] =  "ایمیل فعالسازی مجددا برای شما ارسال شد .لطفا روی لینک فعالسازی موجود در ایمیل کلیک کنید ."; 
	$lang["MESSAGE_USER_DOES_NOT_EXIST"] =  "کاربر مورد نظر موجود نیست"; 
	$lang["MESSAGE_USERNAME_BAD_LENGTH"] =  "شماره موبایل می بایست حداقل 2 کاراکتر و حداکثر 64 کاراکتر می باشد"; 
	$lang["MESSAGE_USERNAME_CHANGE_FAILED"] =  "متاسفیم ، تغییر نام کاربری انجام نشد !"; 
	$lang["MESSAGE_USERNAME_CHANGED_SUCCESSFULLY"] =  "شماره موبایل با موفقیت تغییر داده شد . نام کاربری جدید : "; 
	$lang["MESSAGE_USERNAME_EMPTY"] =  "فیلد نام کاربری خالی می باشد"; 
	$lang["MESSAGE_USERNAME_EXISTS"] =  "شماره موبایل وارد شده قبلا در سیستم ثبت شده است ، لطفا شماره دیگری انتخاب کنید یا وارد سایت شوید ."; 
	$lang["MESSAGE_USERNAME_INVALID"] =  "شماره موبایل وارد شده از الگوی تعریف شده پیروی نمی کند : A-z و عدد - حداقل : 2 کاراکتر و حداکثر 64 کاراکتر"; 
	$lang["MESSAGE_USERNAME_SAME_LIKE_OLD_ONE"] =  "متاسفیم شماره موبایل وارد شده برابر با شماره موبایل فعلی شماست ، لطفا یکی دیگر انتخاب کنید ."; 

	// views
	$lang["WORDING_BACK_TO_LOGIN"] =  "برگشت به صفحه ورود"; 
	$lang["WORDING_CHANGE_EMAIL"] =  "تغییر آدرس ایمیل"; 
	$lang["WORDING_CHANGE_PASSWORD"] =  "تغیر رمز عبور"; 
	$lang["WORDING_CHANGE_USERNAME"] =  "تغییر نام کاربری"; 
	$lang["WORDING_CURRENTLY"] =  "فعلی"; 
	$lang["WORDING_EDIT_USER_DATA"] =  "ویرایش اطلاعات کاربری"; 
	$lang["WORDING_EDIT_YOUR_CREDENTIALS"] =  "شما با موفقیت وارد سایت شده اید ."; 
	$lang["WORDING_FORGOT_MY_PASSWORD"] =  "رمزمو فراموش کردم"; 
	$lang["WORDING_LOGIN"] =  "ورود"; 
	$lang["WORDING_LOGOUT"] =  "خروج"; 
	$lang["WORDING_NEW_EMAIL"] =  "آدرس ایمیل جدید"; 
	$lang["WORDING_NEW_PASSWORD"] =  "رمز عبور جدید"; 
	$lang["WORDING_NEW_PASSWORD_REPEAT"] =  "تکرار رمز عبور"; 
	$lang["WORDING_NEW_USERNAME"] =  "شماره موبایل جدید (نمی تواند خالی باشد az,AZ,09 و 2-64 کاراکتر)"; 
	$lang["WORDING_OLD_PASSWORD"] =  "رمز عبور قبلی"; 
	$lang["WORDING_PASSWORD"] =  "رمز عبور"; 
	$lang["WORDING_PROFILE_PICTURE"] =  "آواتار شما  (از gravatar):"; 
	$lang["WORDING_REGISTER"] =  "عضویت"; 
	$lang["WORDING_REGISTER_NEW_ACCOUNT"] =  "ثبت نام"; 
	$lang["WORDING_REGISTRATION_CAPTCHA"] =  "کد امنیتی را وارد کنید"; 
	$lang["WORDING_REGISTRATION_EMAIL"] =  "آدرس ایمیل (لطفا آدرسی واقعی وارد کنید ، لینک تایید حساب کاربری به این ایمیل ارسال می گردد)"; 
	$lang["WORDING_REGISTRATION_PASSWORD"] =  "رمز عبور (حداقل 6 کاراکتر!)"; 
	$lang["WORDING_REGISTRATION_PASSWORD_REPEAT"] =  "تکرار رمز عبور"; 
	$lang["WORDING_REGISTRATION_USERNAME"] =  "نام کاربری (تنها حروف و اعداد , 2 تا 64 کاراکتر)"; 
	$lang["WORDING_REMEMBER_ME"] =  "مرا به خاطر بسپار (برای 2 هفته)"; 
	$lang["WORDING_REQUEST_PASSWORD_RESET"] =  "برای ریست کردن رمز عبور شماره موبایل/آدرس ایمیل خود را وارد کنید تا ایمیلی حاوی توضیحات نحوه ی عملکرد برای شما ارسال گردد :"; 
	$lang["WORDING_RESET_PASSWORD"] =  "ریست کردن رمز عبور"; 
	$lang["WORDING_SUBMIT_NEW_PASSWORD"] =  "تغییر رمز عبور"; 
	$lang["WORDING_USERNAME"] =  "نام کاربری"; 
	$lang["WORDING_YOU_ARE_LOGGED_IN_AS"] =  "شما وارد شدید : "; 
	return $lang;
}