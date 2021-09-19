<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 640px) {
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}
.header {
width: 100% !important;
padding-top: 50px !important;
padding-bottom: 50px !important;
}
.action .button {
  width: 100% !important;
}
.app-markets {
  width: 100% !important;
}
.footer-link {
  width: 100% !important;
  display: block !important;
}
.footer-link__pipe {
  display: none !important;
}
.inner-body {
  background-image: none !important;
}
.logo {
  height: 36px !important;
  max-height: 36px !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
.app-markets__col {
  padding-left: 5px !important;
  padding-right: 5px !important;
}
}
</style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{{ $header ?? '' }}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="{{ $bodyBg ? 'background-image: url(' . $bodyBg . ')' : '' }}">
<!-- Body content -->
<tr>
<td class="content-cell">
{{ Illuminate\Mail\Markdown::parse($slot) }}

{{ $subcopy ?? '' }}
</td>
</tr>
</table>
</td>
</tr>

{{ $footer ?? '' }}
</table>
</td>
</tr>
</table>
</body>
</html>
