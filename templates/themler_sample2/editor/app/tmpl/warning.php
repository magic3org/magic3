<style>
    html {
        background: #eee;
    }
    body {
        background: #fff;
        color: #333;
        font-family: "Open Sans", sans-serif;
        margin: 2em auto;
        padding: 1em 2em;
        max-width: 700px;
        -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
        box-shadow: 0 1px 3px rgba(0,0,0,0.13);
        word-break: break-word;
    }
    h1 {
        border-bottom: 1px solid #dadada;
        clear: both;
        color: #666;
        font: 24px "Open Sans", sans-serif;
        margin: 30px 0 0 0;
        padding: 0;
        padding-bottom: 7px;
    }
    #error-page {
        margin-top: 50px;
    }
    #error-page p {
        font-size: 14px;
        line-height: 1.5;
        margin: 25px 0 20px;
    }
    #error-page code {
        font-family: Consolas, Monaco, monospace;
    }
    ul li {
        margin-bottom: 10px;
        font-size: 14px ;
    }
    a {
        color: #21759B;
        text-decoration: none;
    }
    a:hover {
        color: #D54E21;
    }
    .button {
        background: #f7f7f7;
        border: 1px solid #cccccc;
        color: #555;
        display: inline-block;
        text-decoration: none;
        font-size: 13px;
        line-height: 26px;
        height: 28px;
        margin: 0;
        padding: 0 10px 1px;
        cursor: pointer;
        -webkit-border-radius: 3px;
        -webkit-appearance: none;
        border-radius: 3px;
        white-space: nowrap;
        -webkit-box-sizing: border-box;
        -moz-box-sizing:    border-box;
        box-sizing:         border-box;

        -webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
        box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
        vertical-align: top;
    }

    .button.button-large {
        height: 29px;
        line-height: 28px;
        padding: 0 12px;
    }

    .button:hover,
    .button:focus {
        background: #fafafa;
        border-color: #999;
        color: #222;
    }

    .button:focus  {
        -webkit-box-shadow: 1px 1px 1px rgba(0,0,0,.2);
        box-shadow: 1px 1px 1px rgba(0,0,0,.2);
    }

    .button:active {
        background: #eee;
        border-color: #999;
        color: #333;
        -webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
        box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
    }
</style>
<h2>Insufficient permissions.</h2>
<p>
    The theme cannot be edited. Please make sure that the user and group running web server is granted the appropriate
    read, write and execute(linux only) permissions on the following folders. As well as read and write permission on
    the files in these folders:
</p>
{folders}
<p>How to do this for MacOS or Linux systems:</p>
<ol>
    <li>login ssh/terminal under privileged user, get sufficient access rights if need using sudo or su to make next changes</li>
    <li>cd {root}</li>
    <li>
        <div>chmod -R u=rwX,g=rX folder_name</div>
        <div><i>For example: chmod -R u=rwX,g=rX app/code/local</i></div>
    </li>
    <li>
        <div>chown -R &#60;user>:&#60;group> folder_name</div>
        <div><i>For example: chown -R apache:apache app/code/local</i></div>
    </li>
</ol>
<p>
    <b>Note</b>: It is general approach. We would recommend that you ask your hosting administrator to grant access
    permissions for listed folders and files.
</p>