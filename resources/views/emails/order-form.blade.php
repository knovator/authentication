<!DOCTYPE html>
<html>
<head>
    <title>
        {{$companyName}}
    </title>
</head>
<style>
    body {
        box-shadow: 0 5px 20px rgba(215, 215, 215, 0.4);
    }

    header,
    footer {
        display: flex;
        align-items: center;
        padding: 5px 20px;
        background: #f4f8fb;
    }

    header h3 {
        width: fit-content;
        display: inline-block;
        padding: 0 5px;
    }

    footer {
        border-bottom: 2px solid rgba(41, 116, 225, 0.7);
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
    }

    footer small {
        text-align: center;
        width: 100%;
        opacity: 0.7;
    }
</style>
<body>
<header>
    <img alt="Jenny Texto Feb" src="{{asset('img/logo.png')}}" width="40" height="40"/>
    <h3>{{$companyName}}</h3>
</header>
<main>
      <pre>
        Dear Customer,
              As per your requirement here we attached order form of based on estimation.
      </pre>
    <pre>
        Your faithfully,
        {{$companyName}}
      </pre>
</main>
<footer>
    <small>Copyright {{$companyName}} @2019</small>
</footer>
</body>
</html>
