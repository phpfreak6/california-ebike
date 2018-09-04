<?php
echo "<style>
  /* The wf-dhl-model (background) */
  .wf-dhl-model {
      display: none;
      position: fixed;
	  z-index: 10000;
	  padding-top: 40px;
	  right: 0;
	  top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: rgb(0,0,0); /* Fallback color */
      box-shadow: 1px 1px 10px lightgrey;
      background-color: rgba(0,0,0,0.8); /* Black w/ opacity */
  }

  /* wf-dhl-model Content */
  .wf-dhl-model-content {
    background-color: #fefefe;
    margin: auto;
    padding: 0px;
    border: 1px solid #888;
    width: 60%;
  }

  /* The wf-dhl-close Button */
  .wf-dhl-close {
    color: #aaaaaa;
    float: right;
    padding-right: 5px;
    font-size: 28px;
    font-weight: bold;
  }

  .wf-dhl-close:hover,
  .wf-dhl-close:focus {
      color: #000;
      text-decoration: none;
      cursor: pointer;
  }
  .wf-dhl-return-close {
      color: #aaaaaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
  }

  .wf-dhl-return-close:hover,
  .wf-dhl-return-close:focus {
      color: #000;
      text-decoration: none;
      cursor: pointer;
  }
/* ================ The wf-dhl-wf-dhl-timeline ================ */

.wf-dhl-wf-dhl-timeline {
  position: relative;
  width: 400px;
  margin: 0 auto;
  margin-top: 20px;
  padding: 1em 0;
  list-style-type: none;
}

.wf-dhl-wf-dhl-timeline:before {
  position: absolute;
  left: 10%;
  top: 0;
  content: ' ';
  display: block;
  width: 6px;
  height: 100%;
  margin-left: -3px;
  background: rgb(201, 201, 201);
  background: -moz-linear-gradient(top, rgba(201, 201, 201, 0) 0%, rgb(201, 201, 201) 8%, rgb(201, 201, 201) 92%, rgba(201, 201, 201, 0) 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(30,87,153,1)), color-stop(100%,rgba(125,185,232,1)));
  background: -webkit-linear-gradient(top, rgba(201, 201, 201, 0) 0%, rgb(201, 201, 201) 8%, rgb(201, 201, 201) 92%, rgba(201, 201, 201, 0) 100%);
  background: -o-linear-gradient(top, rgba(201, 201, 201, 0) 0%, rgba(201, 201, 201) 8%, rgba(201, 201, 201) 92%, rgba(201, 201, 201, 0) 100%);
  background: -ms-linear-gradient(top, rgba(201, 201, 201, 0) 0%, rgb(201, 201, 201) 8%, rgb(201, 201, 201) 92%, rgba(201, 201, 201, 0) 100%);
  background: linear-gradient(to bottom, rgba(201, 201, 201, 0) 0%, rgb(201, 201, 201) 8%, rgb(201, 201, 201) 92%, rgba(201, 201, 201,0) 100%);
  
  z-index: 5;
}

.wf-dhl-wf-dhl-timeline li {
  padding: 1em 0;
}

.wf-dhl-wf-dhl-timeline li:after {
  content: '';
  display: block;
  height: 0;
  clear: both;
  visibility: hidden;
}

.wf-dhl-direction-l {
  position: relative;
  width: 300px;
  float: left;
  text-align: right;
}

.wf-dhl-direction-r {
  position: relative;
  width: 300px;
  float: left;
  padding-left: 17.5%;
}

.wf-dhl-wf-dhl-flag-wrapper {
  position: relative;
  display: inline-block;
  
  text-align: center;
}

.wf-dhl-flag {
  position: relative;
  display: inline;
  background: rgb(248,248,248);
  padding: 6px 10px;
  border-radius: 5px;
  
  font-weight: 600;
  text-align: left;
}

.wf-dhl-direction-l .wf-dhl-flag {
  -webkit-box-shadow: -1px 1px 1px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.15);
  -moz-box-shadow: -1px 1px 1px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.15);
  box-shadow: -1px 1px 1px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.15);
}

.wf-dhl-direction-r .wf-dhl-flag {
  -webkit-box-shadow: 1px 1px 1px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.15);
  -moz-box-shadow: 1px 1px 1px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.15);
  box-shadow: 1px 1px 1px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.15);
}

.wf-dhl-direction-l .wf-dhl-flag:before,
.wf-dhl-direction-r .wf-dhl-flag:before {
  position: absolute;
  top: 50%;
  right: -40px;
  content: ' ';
  display: block;
  width: 12px;
  height: 12px;
  margin-top: -10px;
  background: #fff;
  border-radius: 10px;
  border: 4px solid rgb(186, 12, 47);
  z-index: 10;
}

.wf-dhl-direction-r .wf-dhl-flag:before {
  left: -40px;
}

.wf-dhl-direction-l .wf-dhl-flag:after {
  content: '';
  position: absolute;
  left: 100%;
  top: 50%;
  height: 0;
  width: 0;
  margin-top: -8px;
  border: solid transparent;
  border-left-color: rgb(248,248,248);
  border-width: 8px;
  pointer-events: none;
}

.wf-dhl-direction-r .wf-dhl-flag:after {
  content: '';
  position: absolute;
  right: 100%;
  top: 50%;
  height: 0;
  width: 0;
  margin-top: -8px;
  border: solid transparent;
  border-right-color: rgb(248,248,248);
  border-width: 8px;
  pointer-events: none;
}

.wf-dhl-wf-dhl-time-wrapper {
  display: inline;
  
  line-height: 1em;
  font-size: 0.66666em;
  color: rgb(250,80,80);
  vertical-align: middle;
}

.wf-dhl-direction-l .wf-dhl-wf-dhl-time-wrapper {
  float: left;
}

.wf-dhl-direction-r .wf-dhl-wf-dhl-time-wrapper {
  float: right;
}

.wf-dhl-time {
  display: inline-block;
  padding: 4px 6px;
  background: rgb(248,248,248);
}

.wf-dhl-desc {
  margin: 1em 0.75em 0 0;
  
  font-size: 0.77777em;
  font-style: italic;
  line-height: 1.5em;
}

.wf-dhl-direction-r .wf-dhl-desc {
  margin: 1em 0 0 0.75em;
}

/* ================ wf-dhl-wf-dhl-timeline Media Queries ================ */

@media screen and (max-width: 660px) {

.wf-dhl-wf-dhl-timeline {
  width: 100%;
  padding: 4em 0 1em 0;
}

.wf-dhl-wf-dhl-timeline li {
  padding: 2em 0;
}

.wf-dhl-direction-l,
.wf-dhl-direction-r {
  float: none;
  width: 100%;

  text-align: center;
}

.wf-dhl-wf-dhl-flag-wrapper {
  text-align: center;
}

.wf-dhl-flag {
  background: rgb(255,255,255);
  z-index: 15;
}

.wf-dhl-direction-l .wf-dhl-flag:before,
.wf-dhl-direction-r .wf-dhl-flag:before {
  position: absolute;
  top: -30px;
  left: 50%;
  content: ' ';
  display: block;
  width: 12px;
  height: 12px;
  margin-left: -9px;
  background: #fff;
  border-radius: 10px;
  border: 4px solid rgb(255,80,80);
  z-index: 10;
}

.wf-dhl-direction-l .wf-dhl-flag:after,
.wf-dhl-direction-r .wf-dhl-flag:after {
  content: '';
  position: absolute;
  left: 50%;
  top: -8px;
  height: 0;
  width: 0;
  margin-left: -8px;
  border: solid transparent;
  border-bottom-color: rgb(255,255,255);
  border-width: 8px;
  pointer-events: none;
}

.wf-dhl-wf-dhl-time-wrapper {
  display: block;
  position: relative;
  margin: 4px 0 0 0;
  z-index: 14;
}

.wf-dhl-direction-l .wf-dhl-wf-dhl-time-wrapper {
  float: none;
}

.wf-dhl-direction-r .wf-dhl-wf-dhl-time-wrapper {
  float: none;
}

.wf-dhl-desc {
  position: relative;
  margin: 1em 0 0 0;
  padding: 1em;
  background: rgb(245,245,245);
  -webkit-box-shadow: 0 0 1px rgba(0,0,0,0.20);
  -moz-box-shadow: 0 0 1px rgba(0,0,0,0.20);
  box-shadow: 0 0 1px rgba(0,0,0,0.20);
  
  z-index: 15;
}

.wf-dhl-direction-l .wf-dhl-desc,
.wf-dhl-direction-r .wf-dhl-desc {
  position: relative;
  margin: 1em 1em 0 1em;
  padding: 1em;
  
  z-index: 15;
}

}

@media screen and (min-width: 400px ?? max-width: 660px) {

.wf-dhl-direction-l .wf-dhl-desc,
.wf-dhl-direction-r .wf-dhl-desc {
  margin: 1em 4em 0 4em;
}

}

.dhl_clear {
	clear:both;
}
.dhl_content{
s    box-shadow: 1px 1px 10px lightgrey;
    width: 90%;
    margin: 3% auto 0 auto;
    height: auto;
    background-color: #F5F5F5;
}
.dhl_content1 {
	background-color:#c9c9c9;
	text-align:center;
	padding:2em;
}
.dhl_content1 h2 {
	font-family: 'Open Sans', sans-serif;
	text-transform:uppercase;
	margin:0;
	color:#fff;
}
.dhl_content2 {
	background-color:#ffcd00;
}
.dhl_content2-header1 {
	float:left;
	width:27%;
	text-align:center;
	padding:1.dhl_5em;
}
.dhl_content2-header1 p {
	font-size:16px;
	font-weight:700;
	color: #BA0C11;
	margin:0;
}
.dhl_content2-header1 span {
	font-size:14px;
	font-weight:400;
}
.dhl_shipment {
	width:100%;
	margin-top:10%;
}
span.dhl_line {
    height: 5px;
    width: 90px;
    background-color:#c9c9c9;
    display: block;
    position: absolute;
    top: 28%;
    left: 45%;
}
.dhl_confirm{
	text-align:center;
	width:20%;
	position:relative;
	float:left;
	margin-left:15%;
}
.dhl_confirm .dhl_imgcircle , .dhl_process .dhl_imgcircle, .dhl_quality .dhl_imgcircle {
	background-color:#c9c9c9;
	
}
.dhl_confirm span.dhl_line, .dhl_process span.dhl_line {
	background-color:#c9c9c9; 
	
}
.dhl_content3 p {
	margin-left:-50%;
	font-size:15px;
	font-weight:600;
} 
.dhl_imgcircle {
	height:75px;
	width:75px;
	border-radius:50%;
	background-color:#c9c9c9;
	position:relative;
}
.dhl_imgcircle img {
	height:30px;
	position:absolute;
	top: 28%;
	left: 30%;
}
.dhl_process{
	position:relative;
	width:20%;
	text-align:center;
	float:left;
}
.dhl_quality {
	position:relative;
	width:20%;
	text-align:center;
	float:left;
}
.dhl_dispatch{
	position:relative;
	width:20%;
	text-align:center;
	float:left;
}
.dhl_delivery{
	position:relative;
	width:20%;
	text-align:center;
	float:left;
	margin-right:-9%;
}
.dhl_footer a, a:active {
	color:grey;
	text-decoration:none;
	font-family: 'Tahoma', sans-serif;
}
.dhl_footer a:hover {
	color:#00c4ff;
	text-decoration:none;
	transition:all 0.dhl_5s ease-in-out;
}
.dhl_footer {
	margin-top:3%;
	text-align:center;
	font-weight:100;
}
.dhl_footer p {
	color:grey;
	font-size:15px;
	font-family: 'Tahoma', sans-serif;
	line-height:25px;
}

/*---- responsive-design -----*/
@media(max-width:1920px){
	span.dhl_line {
	width:157px;
	left:32%;
	}
	.dhl_shipment{
		margin-top:6%;
	}
.dhl_content3 p{
margin-left:-65%;
}
}

@media(max-width:1680px){
	.dhl_content3 p {
    margin-left: -60%;
}
span.dhl_line {
    width: 127px;
    left: 37%;
}
}

@media(max-width:1600px){
span.dhl_line {
    width: 117px;
    left: 39%;
}
}

@media(max-width:1440px){
.dhl_content3 p {
    margin-left: -53%;
}
span.dhl_line {
    width: 99px;
    left: 43%;
}
}

@media (max-width: 1366px){
span.dhl_line {
    width: 90px;
    left: 45%;
}
.dhl_shipment {
    margin-top: 10%;
}
}

@media (max-width: 1280px){
span.dhl_line {
    width: 80px;
    left: 48%;
	top:29%;
}
}

@media (max-width: 1080px){
.dhl_content {
width: 75%;
}
span.dhl_line {
    width: 88px;
left: 46%;
}
}

@media (max-width: 1050px){
span.dhl_line {
    width: 84px;
    left: 47%;
}
}

@media (max-width: 1024px){
	.dhl_content{
		width:77%;
	}
	.dhl_content3 p {
		font-size:14px;
	}
}

@media (max-width: 991px){
	.dhl_content {
    width: 80%;
}
span.dhl_line {
    width: 84px;
    left: 47%;
}
}

@media (max-width: 900px){
.dhl_content {
    width: 85%;
}
span.dhl_line {
    width: 78px;
    left: 49%;
}
}

@media (max-width: 800px){
.dhl_content {
    width: 95%;
}
.dhl_content2-header1 p {
	margin: 0 0 0 -7%;
}
}

@media (max-width: 768px){
	.dhl_content {
    width: 90%;
}
.dhl_content2-header1 {
	width: 25%;
}
.dhl_content2-header1 p {
    margin: 0 -19% 0 -10%;
}
span.dhl_line {
    width: 72px;
    left: 51%;
}
}

@media (max-width: 736px){
	span.dhl_line {
    width: 62px;
    left: 55%;
}
}

@media (max-width: 667px){
	.dhl_content2-header1 p {
	font-size:14px;
	}
	.dhl_content2-header1 span {
    font-size: 13px;
}
.dhl_shipment {
    margin-top: 13%;
}
.dhl_content3 p {
    font-size: 12px;
	margin-left: -35%;
}
.dhl_confirm{
	margin-left:4%;
}
span.dhl_line {
    width: 49px;
    left: 60%;
}
}

@media (max-width: 600px){
	.dhl_content1 {
		padding:1.dhl_2em;
	}
.dhl_content2-header1 p {
    font-size: 13px;
}
.dhl_content2-header1 span {
    font-size: 12px;
}
.dhl_content2-header1 {
    width: 24%;
}
.dhl_imgcircle {
    height: 65px;
    width: 65px;
}
.dhl_imgcircle img{
	top: 26%;
    left: 27%;
}
.dhl_content3 p {
	margin-left: -38%;
	font-size:11px;
}
.dhl_content {
	height: 395px;
}
span.dhl_line {
    width: 50px;
    left: 58%;
}
}

@media (max-width: 568px){
	.dhl_content{
		height:380px;
}
	.dhl_content1{
	padding: 1em;
}
	span.dhl_line {
    width: 56px;
    left: 47%;
}
	.dhl_content2-header1 {
    width: 23%;
}
	.dhl_imgcircle {
    height: 50px;
    width: 50px;
}
	.dhl_imgcircle img {
    height: 25px;
    top: 27%;
    left: 25%;
}
	.dhl_content3 p {
    font-size: 10px;
    margin-left: -46%;
}
	.dhl_confirm {
    margin-left: 5%;
}
}

@media (max-width: 414px){
	.dhl_header {
    margin-top: 8%;
}
	.dhl_content {
    width: 70%;
	height:750px;
	margin-top:9%;
	padding:10%;
}
	.dhl_content1 {
	margin: -14% 0 0 -14%;
	width:116%;
}
	.dhl_content1 h2 {
	font-size:22px;
}
	.dhl_content2 {
    margin-left: -14%;
	width: 127.dhl_5%;
}
	.dhl_content2-header1 {
	padding:0.dhl_7em;
    width: 80%;
	margin-left: 3%;
}
	.dhl_content2-header1 p {
    font-size: 19px;
}
	.dhl_content2-header1 span {
    font-size: 16px;
}
	.dhl_confirm {
	width:100%;
}
	.dhl_process {
	width:100%;
	margin: 22% 0 0 5%;
}
	.dhl_quality{
	width:100%;
	margin: 22% 0 0 5%;
}
	.dhl_dispatch{
	width:100%;
	margin: 22% 0 0 5%;
}
	.dhl_delivery{
	width:100%;
	margin: 22% 0 0 5%;
}
	.dhl_imgcircle {
    
	height: 70px;
    width: 70px;
	margin-left: 35%;
}
	.dhl_imgcircle img {
    height: 30px;
    top: 27%;
    left: 28%;
}
	span.dhl_line {
    width: 6px;
    left: 46%;
    height: 48px;
	top:124%;
}
	.dhl_content3 p {
    font-size: 15px;
    margin: -16% 0 4% -81%;
}
	.dhl_shipment {
    margin-left: 16%;
}
	.dhl_footer {
	padding:1%;
}
	.dhl_footer p {
	font-size:16px;
}
}

@media (max-width: 384px){
	.dhl_header {
    margin-top: 9%;
}
	.dhl_content1 {
	width:115%;
}
	.dhl_content1 h2 {
    font-size: 21px;
}
	.dhl_content3 p {
    margin: -18% 0 6% -85%;
}
	.dhl_shipment {
    margin-top: 15%;
}
	span.dhl_line {
	top:118%;
	left:47%;
	height:47px;
}
	.dhl_content {
    height: 770px;
}
	.dhl_footer {
    padding: 3%;
}
	.dhl_footer p {
    font-size: 15px;
}
}

@media (max-width: 375px){
	.dhl_content {
    height: 755px;
	width:68%;
}
	.dhl_content2{
	width:128%;
}
	.dhl_content1 h2 {
    font-size: 19px;
}
	.dhl_content3 p {
    margin: -18% 0 8% -86%;
}
	span.dhl_line {
    top: 105%;
    left: 47.dhl_5%;
    height: 52px;
}
	.dhl_shipment {
    margin-left: 17%;
}
}

@media (max-width: 320px){
	.dhl_header {
    margin-top: 10%;
}
	.dhl_content{
	width:66%;
	margin-top: 10%;
	padding:12%;
    height: 709px;
}
	.dhl_content1 {
    padding: 0.dhl_7em;
	width:125%;
	margin:-18% 0 0 -18%;
}
	.dhl_header h1{
	font-size:30px;
}
	.dhl_content2 {
	margin-left: -18%;
	width: 136.dhl_5%;
}
	.dhl_content1 h2 {
    font-size: 16px;
}
	.dhl_content2-header1 span {
    font-size: 15px;
}
	.dhl_content3 p {
    margin: -23% 0 12% -99%;
}
	.dhl_shipment {
	margin: 16% 0 0 19%;
}
	span.dhl_line {
    top: 102%;
    left: 50%;
	height:44px;
}
	.dhl_footer {
	margin-top: 1%;
}
	.dhl_footer p {
    font-size: 14px;
}
}


</style>";
