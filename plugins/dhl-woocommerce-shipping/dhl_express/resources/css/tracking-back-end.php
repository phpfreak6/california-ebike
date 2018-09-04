<?php
echo "  <style>
  /* The wf-dhl-model (background) */
  .wf-dhl-model {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1; /* Sit on top */
      padding-top: 100px; /* Location of the box */
      right: 0;
      top: 0;
      width: 45%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
  }

  /* wf-dhl-model Content */
  .wf-dhl-model-content {
      background-color: #fefefe;
      margin: auto;
      padding: 20px;
      box-shadow: 2px 2px 20px lightgrey;
      width: 80%;
  }

  /* The wf-dhl-close Button */
  .wf-dhl-close {
      color: #aaaaaa;
      float: right;
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
  background: rgb(80,80,80);
  background: -moz-linear-gradient(top, rgba(80,80,80,0) 0%, rgb(80,80,80) 8%, rgb(80,80,80) 92%, rgba(80,80,80,0) 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(30,87,153,1)), color-stop(100%,rgba(125,185,232,1)));
  background: -webkit-linear-gradient(top, rgba(80,80,80,0) 0%, rgb(80,80,80) 8%, rgb(80,80,80) 92%, rgba(80,80,80,0) 100%);
  background: -o-linear-gradient(top, rgba(80,80,80,0) 0%, rgb(80,80,80) 8%, rgb(80,80,80) 92%, rgba(80,80,80,0) 100%);
  background: -ms-linear-gradient(top, rgba(80,80,80,0) 0%, rgb(80,80,80) 8%, rgb(80,80,80) 92%, rgba(80,80,80,0) 100%);
  background: linear-gradient(to bottom, rgba(80,80,80,0) 0%, rgb(80,80,80) 8%, rgb(80,80,80) 92%, rgba(80,80,80,0) 100%);
  
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
  border: 4px solid rgb(255,80,80);
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
</style>";
