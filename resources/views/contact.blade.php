<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Техническая поддержка Честная Марка</title>
       
        <link rel="stylesheet" href="{{ URL::asset("assets/css/app.css") }}">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
       

    </head>
    <body class="antialiased main-body">
      <section>
        <div class="row">
          <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="logo">
              <img src="image/logo-2.svg" />
            </div>
          </div>
          <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="contact-info">
              <span><i class="fa fa-phone fa-1x" aria-hidden="true"></i><a href="tel:88007004898">8 800 700 48 98</a></span>
            
            </div>
          </div>
        </div>
      </section>
          <section>
        <form class="form" id="form-contact" action="/landing" method="POST">
            {{csrf_field()}}
            
            <h2 class="contact-head">Свяжитесь с нами</h2>
       
            <p type="Название компании"><input id="name_ticket" name="name_ticket" placeholder="Укажите название кампании"></input></p>
            <p type="Ваш Email"><input id="email_ticket" name="email_ticket" placeholder="Укажите ваш email, чтобы специалист смог вам ответить"></input></p>
            <p type="Тема обращения"><input id="subject_ticket" name="subject_ticket" placeholder="Введите тему обращения"></input></p>
            
           
            <p type="Обращение"><textarea class="textarea-contact" id="text_ticket" name="text_ticket" placeholder="Расскажите о вашей проблеме"></textarea></p>
           
            <p style="text-align: center"><button type="submit" class="sub-but center-block">Отправить</button></p>
            @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
            @if (count($errors) > 0)
            <div class="alert alert-danger">
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          </form>
          </section>
    </body>
</html>
