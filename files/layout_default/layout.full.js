var waiting_for_response = !1,
  force_button_disable = !1,
  buttons_disabled = !1;

function loadPage(
  url = null,
  target = "all",
  data = null,
  method = "post",
  hide = !1
) {
  if (!buttons_disabled) {
    console.log("loading...");
    if (
      (null == url
        ? (url = window.location.href.replace(
            /&act=wake|&act=sleep|&act=gather|&act=equip|&act=delete|&process=split|&process=merge|&process=take_out|&forget=\d*|&start=\d*|&quit=\d*|&track=\d*|&invID=\d*|&turn_in=\d*|&dialog_option=[^&]*/gi,
            ""
          ))
        : history.pushState(null, null, url),
      null != data && "string" === $.type(data)
        ? ((tempData = data.split(",")),
          (data = {}),
          $.each(tempData, function(t, e) {
            (moreTempData = e.split(":")),
              (data[moreTempData[0]] = moreTempData[1]);
          }),
          hide || (data.ajaxRequest = "yes"))
        : hide || ((data = {}), (data.ajaxRequest = "yes")),
      "all" == target && (target = "#page-wrapper-2"),
      "get" == method)
    );
    else if ("post" == method) {
      (waiting_for_response = !0),
        $(target).load(url + " " + target, data, function(response) {
          enableButtons();
          var response_temp = response.split("<!DOCTYPE"),
            error = response_temp[0];
          if (
            ((response_temp = response_temp.splice(1)),
            (response = ""),
            response_temp.forEach(function(t) {
              response = response + "<!DOCTYPE" + t;
            }),
            error.length > 0 && (console.log(error), alert(error)),
            $(target)
              .children(":first")
              .unwrap(),
            0 == $("#page-wrapper-2").length &&
              $("#page-wrapper-1")
                .children()
                .wrapAll('<div id="page-wrapper-2" />'),
            (waiting_for_response = !1),
            manageScrollBars(),
            "function" == typeof summary_script && summary_script(),
            $(response).find("#confirm_popups").length &&
              eval($(response).find("#confirm_popups")[0].innerHTML),
            $(response).find("#BattlePageJS").length &&
              (!("timer" in window) || !timer))
          )
            var battle_page_fix = setInterval(function() {
              if (
                $(response).find("#BattlePageJS").length &&
                (!("timer" in window) ||
                  !timer ||
                  typeof pageInit === "undefined")
              ) {
                var t = $(response).find("#the_id")[0].outerHTML;
                $("body").append(t);
                t = $(response).find("#BattlePageJS")[0].outerHTML;
                $("body").append(t), clearInterval(battle_page_fix);
                pageInit();
              } else {
                pageInit;
              }
            }, 50);
          $(response).closest("#key_bindings").length &&
            (resetMouseTrap(),
            eval($(response).closest("#key_bindings")[0].innerHTML)),
            loadAds();
          if ($(response).find("#pageScript").length) {
            eval($(response).find("#pageScript")[0].innerHTML);
          }
          console.log("...finished");
        }),
        (force_button_disable = !0),
        disableButtons(),
        (i = 0);
      var release_buttons = setInterval(function(t = 0) {
        !waiting_for_response &&
          t < 4 &&
          ((force_button_disable = !1),
          enableButtons(),
          clearInterval(release_buttons)),
          (t += 1);
      }, 50);
    }
    disableButtons(),
      setTimeout(function() {
        $("[title]").colorTip(),
          "function" == typeof setUpTerritoryMap && setUpTerritoryMap();
      }, 500);
  } else {
    console.log("buttons paused");
  }
}

async function loadAds() {
  $time_cookie = getCookie("adTimer");
  if ($time_cookie == undefined || $time_cookie < Date.now() - 10) {
    setCookie("adTimer", Date.now());
    (adsbygoogle = window.adsbygoogle || []).push({});
  }
}

function disableButtons() {
  buttons_disabled = !0;
}

function enableButtons() {
  disableButtons(), force_button_disable || (buttons_disabled = !1);
}
if ("undefined" == typeof variable) var timers = [];

function tickTimer(t) {
  if (!timers.includes(t)) {
    if ((timers.push(t), !$("#" + t).length)) return !1;
    if ("n/a" == $("#" + t).data("timer-seconds"))
      return "Show" == $("#" + t).data("show") && $("#" + t).html("n/a"), !1;
    var e = Math.round(new Date().getTime() / 1e3),
      a = parseInt($("#" + t).data("timer-seconds")),
      o = e + a,
      n = Math.floor(a / 86400).toString(),
      i = Math.floor((a - 86400 * n) / 3600).toString(),
      r = Math.floor((a - 86400 * n - 3600 * i) / 60).toString(),
      l = (a - 86400 * n - 3600 * i - 60 * r).toString();
    if (
      null != $("#" + t).data("refresh") &&
      "updateProfile" == $("#" + t).data("refresh")
    ) {
      $regen = parseInt($("#" + t).data("regen"));
      var s = setInterval(function() {
        var t = $("#widget-user-details-health-text")
            .text()
            .split("/"),
          e = $("#widget-user-details-chakra-text")
            .text()
            .split("/"),
          a = $("#widget-user-details-stamina-text")
            .text()
            .split("/"),
          o = {
            health: {
              new: Math.floor(parseInt(t[0]) + $regen),
              max: Math.floor(parseInt(t[1]))
            },
            chakra: {
              new: Math.floor(parseInt(e[0]) + $regen),
              max: Math.floor(parseInt(e[1]))
            },
            stamina: {
              new: Math.floor(parseInt(a[0]) + $regen),
              max: Math.floor(parseInt(a[1]))
            }
          };
        for (var n in o)
          if (o.hasOwnProperty(n)) {
            var i = o[n];
            i.max < i.new && (i.new = i.max),
              $("#widget-user-details-" + n + "-text").text(
                i.new + " / " + i.max
              ),
              $("#widget-user-details-" + n + "-fill").css(
                "width",
                Math.floor((i.new / i.max) * 100) + "px"
              );
          }
      }, 1e3);
    } else {
      if (
        ("Show" == $("#" + t).data("show") &&
          (n < 1 && i < 1 && r < 1
            ? $("#" + t).html(l + "s")
            : n < 1 && i < 1
            ? $("#" + t).html(r + "m : " + l + "s")
            : n < 1
            ? $("#" + t).html(i + "h : " + r + "m : " + l + "s")
            : $("#" + t).html(n + "d : " + i + "h : " + r + "m : " + l + "s")),
        $("#" + t).data("active", "yes"),
        a > 1)
      )
        s = setInterval(function() {
          $("#" + t).data("active", "yes"),
            (a = o - Math.round(new Date().getTime() / 1e3));
          var e = Math.floor(a / 86400).toString(),
            n = Math.floor((a - 86400 * e) / 3600).toString(),
            i = Math.floor((a - 86400 * e - 3600 * n) / 60).toString(),
            r = (a - 86400 * e - 3600 * n - 60 * i).toString();
          "Show" == $("#" + t).data("show") &&
            (e < 1 && n < 1 && i < 1
              ? $("#" + t).html(r + "s")
              : e < 1 && n < 1
              ? $("#" + t).html(i + "m : " + r + "s")
              : e < 1
              ? $("#" + t).html(n + "h : " + i + "m : " + r + "s")
              : $("#" + t).html(
                  e + "d : " + n + "h : " + i + "m : " + r + "s"
                )),
            a <= 0.99999999 &&
              (clearInterval(s),
              "function" == typeof window[$("#" + t).data("callback")]
                ? window[$("#" + t).data("callback")].apply(null, null)
                : window.location.href.includes("?id=86&")
                ? (window.location.href = window.location.href
                    .split("&")
                    .slice(0, 2)
                    .join("&"))
                : (window.location.href = window.location.href));
        }, 1e3);
      a < 1 &&
        ("function" == typeof window[$("#" + t).data("callback")]
          ? window[$("#" + t).data("callback")].apply(null, null)
          : window.location.href.includes("?id=86&")
          ? (window.location.href = window.location.href
              .split("&")
              .slice(0, 2)
              .join("&"))
          : (window.location.href = window.location.href));
    }
  }
}

function manageScrollBars() {
  var t, e;
  $("#left-bar").scrollTop(getCookie("left-bar-scroll-top")),
    $("#right-bar").scrollTop(getCookie("right-bar-scroll-top")),
    $("#left-bar").scroll(function(e) {
      t && clearTimeout(t),
        (t = setTimeout(function() {
          setCookie("left-bar-scroll-top", e.target.scrollTop);
        }, 100));
    }),
    $("#right-bar").scroll(function(t) {
      e && clearTimeout(e),
        (e = setTimeout(function() {
          setCookie("right-bar-scroll-top", t.target.scrollTop);
        }, 100));
    });
}

function refreshPage() {
  window.location.href.includes("?id=86&")
    ? (window.location.href = window.location.href
        .split("&")
        .slice(0, 2)
        .join("&"))
    : (window.location.href = window.location.href);
}

function doNothing() {}

function setCookie(t, e, a) {
  var o = "";
  if (a) {
    var n = new Date();
    n.setTime(n.getTime() + 24 * a * 60 * 60 * 1e3),
      (o = "; expires=" + n.toUTCString()),
      console.log("Set expiration date: " + n.toUTCString());
  }
  document.cookie = t + "=" + (e || "") + o + "; path=/";
}

function getCookie(t) {
  var e = ("; " + document.cookie).split("; " + t + "=");
  if (2 == e.length)
    return e
      .pop()
      .split(";")
      .shift();
}

function autoUpdateChat() {}
$(window).on("load", function() {
  $(document).on("click", "#ui-button-top-right", function() {
    $("#right-bar").hasClass("active")
      ? ($("#right-bar").removeClass("active"),
        $("#ui-button-top-right").removeClass("active"),
        $("#widget-notifications-floating-counter").fadeIn(),
        $("body").hasClass("menu-open-right") &&
          ($("body").removeClass("menu-open-right"),
          $("#center-bar").removeClass("menu-open")),
        setCookie("right-bar-open", !1))
      : ($("#right-bar").addClass("active"),
        $("#ui-button-top-right").addClass("active"),
        $("#widget-notifications-floating-counter").hide(),
        $(window).width() > 500 &&
          ($("body").addClass("menu-open-right"),
          $("#center-bar").addClass("menu-open"),
          setCookie("right-bar-open", !0),
          setCookie("left-bar-open", !1))),
      $("body").removeClass("menu-open-left"),
      $("#left-bar").hasClass("active") &&
        ($("#left-bar").toggleClass("active"),
        $("#ui-button-top-left").toggleClass("active"),
        setCookie("left-bar-open", !1));
  }),
    $(document).on("click", "#ui-button-top-left", function() {
      $("#left-bar").hasClass("active")
        ? ($("#left-bar").removeClass("active"),
          $("#ui-button-top-left").removeClass("active"),
          $("body").hasClass("menu-open-left") &&
            ($("body").removeClass("menu-open-left"),
            $("#center-bar").removeClass("menu-open")),
          setCookie("left-bar-open", !1))
        : ($("#left-bar").addClass("active"),
          $("#ui-button-top-left").addClass("active"),
          $(window).width() > 500 &&
            ($("body").addClass("menu-open-left"),
            $("#center-bar").addClass("menu-open"),
            setCookie("left-bar-open", !0),
            setCookie("right-bar-open", !1))),
        $("body").removeClass("menu-open-right"),
        $("#right-bar").hasClass("active") &&
          ($("#right-bar").toggleClass("active"),
          $("#ui-button-top-right").toggleClass("active"),
          setCookie("right-bar-open", !1));
    });
  [
    "notifications",
    "quests",
    "side-menu",
    "user-portrait",
    "user-details",
    "travel",
    "quick-links"
  ].forEach(function(t) {
    $(document).on("click", "#widget-" + t + "-title", function(e) {
      e.originalEvent.target.id.endsWith("title") &&
        ($("#widget-" + t + "-title").toggleClass("closed"),
        $("#widget-" + t + "-content").slideToggle(),
        $("#widget-" + t + "-title").hasClass("closed")
          ? setCookie("widget-" + t + "-closed", !0)
          : setCookie("widget-" + t + "-closed", !1));
    });
  }),
    $(document).on("click", "#widget-side-menu .side-menu-button", function(t) {
      if (
        $(
          "#widget-side-menu-content #side-menu-" +
            $("#" + t.target.id).data("menu") +
            "-box"
        ).hasClass("active")
      )
        return !1;
      $(".side-menu-type-box.active").removeClass("active"),
        $(
          "#widget-side-menu-content #side-menu-" +
            $("#" + t.target.id).data("menu") +
            "-box"
        ).addClass("active"),
        setCookie("menu-open-tab", $("#" + t.target.id).data("menu"));
    }),
    $(document).on("click", "#widget-top-menu .top-menu-button", function(t) {
      if (
        $(
          "#widget-top-menu #top-menu-" +
            $("#" + t.target.id).data("menu") +
            "-box"
        ).hasClass("active")
      )
        return !1;
      $(".top-menu-type-box.active").removeClass("active"),
        $(
          "#widget-top-menu #top-menu-" +
            $("#" + t.target.id).data("menu") +
            "-box"
        ).addClass("active"),
        setCookie("menu-open-tab", $("#" + t.target.id).data("menu"));
    }),
    $(document).on("click", ".mobile-menu-button", function(t) {
      if (
        $(
          "#widget-mobile-menu #mobile-menu-" +
            $("#" + t.target.id).data("menu") +
            "-box"
        ).hasClass("active")
      )
        return !1;
      $(".mobile-menu-type-box.active").removeClass("active"),
        $(
          "#mobile-menu-" + $("#" + t.target.id).data("menu") + "-box"
        ).addClass("active"),
        setCookie("menu-open-tab", $("#" + t.target.id).data("menu"));
    }),
    $(document).on("click", "#ui-button-bottom-right", function() {
      $("#widget-mobile-menu").fadeToggle(250),
        $("#ui-button-bottom-right").toggleClass("active");
    }),
    $(document).on("click", "#ui-button-bottom-left", function() {
      $("#widget-popup-travel-wrapper").fadeToggle(250),
        $("#ui-button-bottom-left").toggleClass("active"),
        $("#ui-button-bottom-left").hasClass("active")
          ? setCookie("widget-popup-travel-open", !0)
          : setCookie("widget-popup-travel-open", !1);
    }),
    $(document).on("click", "#ui-button-bottom-center", function() {
      $("#widget-popup-quick-links-wrapper").fadeToggle(250),
        $("#ui-button-bottom-center").toggleClass("active");
    }),
    $(document).on(
      "click",
      ".toggle-button-info, .toggle-button-drop",
      function(t) {
        t.stopPropagation(),
          $(t.currentTarget).toggleClass("closed"),
          "#" !=
            $(t.currentTarget)
              .data("target")
              .charAt(0) &&
          "." !=
            $(t.currentTarget)
              .data("target")
              .charAt(0)
            ? $("#" + $(t.currentTarget).data("target")).toggleClass("closed")
            : $($(t.currentTarget).data("target")).toggleClass("closed");
      }
    ),
    $(document).on("click", ".table-legend, .table-legend-mobile", function(t) {
      var e = t.target.parentElement,
        a = 0;
      $(t.target.classList).each(function(t, e) {
        e.startsWith("column-") && (a = e);
      }),
        "up" == $(t.target).data("direction")
          ? $("." + a + ".table-legend, ." + a + ".table-legend-mobile").data(
              "direction",
              "down"
            )
          : $("." + a + ".table-legend, ." + a + ".table-legend-mobile").data(
              "direction",
              "up"
            );
      var o = 0;
      o = "up" == $(t.target).data("direction") ? 1 : -1;
      var n = {};
      $("." + a)
        .not(".table-legend")
        .not(".table-legend-mobile")
        .not(".page-pages")
        .not("table-footer")
        .each(function(t, e) {
          var a = "";
          for (
            $(e.classList).each(function(t, e) {
              e.startsWith("row-") && (a = e);
            });
            void 0 !== e.children[0] && null !== e.children[0];

          )
            e = e.children[0];
          n[a] = e.textContent.replace(/[^0-9a-z]/gi, "").toLowerCase();
        });
      var i = Object.keys(n).sort(function(t, e) {
          return n[t] < n[e] ? 1 : n[t] > n[e] ? -1 : 0;
        }),
        r = $(e)
          .find("div")
          .not(".table-legend")
          .not(".page-pages")
          .not(".table-footer");
      r.sort(function(t, e) {
        (aColumn = ""),
          (bColumn = ""),
          (aRow = ""),
          (bRow = ""),
          $(t.classList).each(function(t, e) {
            e.startsWith("row-") && (aRow = e),
              e.startsWith("column-") && (aColumn = e);
          }),
          $(e.classList).each(function(t, e) {
            e.startsWith("row-") && (bRow = e),
              e.startsWith("column-") && (bColumn = e);
          });
        var a = 0;
        if (aRow == bRow) {
          if (aColumn > bColumn) return 1;
          if (aColumn < bColumn) return -1;
        } else
          $(i).each(function(t, e) {
            return e == aRow
              ? ((a = o), !1)
              : e == bRow
              ? ((a = -1 * o), !1)
              : void 0;
          });
        return a;
      }),
        ($lastRow = "nope"),
        ($toggle = "table-alternate-2"),
        $(r).each(function(t, a) {
          $(a).removeClass("table-alternate-1"),
            $(a).removeClass("table-alternate-2"),
            $(a).hasClass($lastRow) ||
              ($(a.classList).each(function(t, e) {
                e.startsWith("row-") && ($lastRow = e);
              }),
              "table-alternate-2" == $toggle
                ? ($toggle = "table-alternate-1")
                : ($toggle = "table-alternate-2")),
            $(a).hasClass("table-legend") || $(a).addClass($toggle),
            $(a).appendTo(e);
        }),
        $(".page-pages").appendTo(e),
        $(".table-footer").appendTo(e);
    }),
    manageScrollBars(),
    $(".count-down").each(function(t, e) {
      tickTimer(e.attributes.id.nodeValue);
    });
  setInterval(function() {
    $(".count-down").each(function(t, e) {
      "yes" != $(e).data("active") &&
        setTimeout(function() {
          "yes" != $(e).data("active") && tickTimer(e.attributes.id.nodeValue);
        }, 2e3);
    });
  }, 250);
  $.each($(".scroll-to-bottom"), function(t, e) {
    $(e).scrollTop($(e)[0].scrollHeight - $(e)[0].clientHeight);
  }),
    $("textarea").on("keyup", function(t) {
      var e = t.target.maxLength;
      if (t.target.value.length > e) return !1;
      e - t.target.value.length <= 0.75 * e
        ? ($(t.target.parentElement)
            .find(".textAreaCounter")
            .html("(" + t.target.value.length + "/" + e + ")"),
          $(t.target.parentElement)
            .find("textarea")
            .addClass("active"))
        : ($(t.target.parentElement)
            .find(".textAreaCounter")
            .html(""),
          $(t.target.parentElement)
            .find("textarea")
            .removeClass("active"));
    });
});
