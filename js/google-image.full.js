var DownloadImgUrl = (function() {
    var currentLineKeyWords = 0;
    var linesKeyWord = [];
    var arrList = {};
    var numDownloadImgOnPage = 0;
    function printPagination(currentPage, countPages) {
        var pageArr = pageArray(currentPage, countPages);
        $("#pagination").html('');
        if(Object.keys(pageArr).length > 0) {
            var html = '<div class="btn-group">';
            $.each(pageArr, function(key, val) {
                if(val == '...') {
                    html += '</div><div class="btn-group">';
                } else if(currentPage == val) {
                    html += '<span class="active btn btn-default">'+val+'</span>';
                } else {
                    html += '<a class="btn btn-default" onClick="DownloadImgUrl.printImages(\''+val+'\');">'+val+'</a>';
                }
            });
            html += '</div>';
            $("#pagination").html(html);
        }
    }
    
    function findBlackDomain(id, url) {
        var parser = document.createElement('a');
        parser.href = url;
        $.getJSON("/stop-words.json", function(json) {
            for(var i = 0; i < json.length; i++) {
                if(parser.hostname.indexOf(json[i]) > -1) {
                    $("#ib"+id).css("background","#F8C804");
                    break;
                }
            }
        });
    }
    
    function escapeHtml(html) {
        html = html.replace(/&/g, "&amp;");
        html = html.replace(/</g, "&lt;");
        html = html.replace(/>/g, "&gt;");
        html = html.replace(/"/g, "&quot;");
        return html; 
    }

    function pageArray(currentPage, countPages) {
        if (countPages == 0 || countPages == 1) return [];
        var pageArr = [];
        if (countPages > 10) {
    		if(currentPage <= 4 || currentPage + 3 >= countPages) {
    			for(var i = 0; i <= 4; i++) {
    				pageArr[i] = i + 1;
    			}
    			pageArr[5] = "...";
    			for(var j = 6, k = 4; j <= 10; j++, k--) {
    				pageArr[j] = countPages - k;
    			}			
    		} else {
    			pageArr[0] = 1;
    			pageArr[1] = 2;
    			pageArr[2] = "...";
    			pageArr[3] = currentPage - 2;
    			pageArr[4] = currentPage - 1;
    			pageArr[5] = currentPage;
    			pageArr[6] = currentPage + 1;
    			pageArr[7] = currentPage + 2;
    			pageArr[8] = "...";
    			pageArr[9] = countPages - 1;
    			pageArr[10] = countPages;
    		}
    	} else {
    		for(var n = 0; n < countPages; n++) {
    			pageArr[n] = n + 1;
    		}
    	}
    	return pageArr;
    }

    function printImg(start, data, newResult) {
        var html;
        var tegA;
        var checkbox;
        var sizeImg;
		var inputTitleImg;
		var inputLinkToOriginal;
		var inputLinkPageImg;
		if(newResult != undefined) {
		    $("#iamgeList").html('');
		}
        $.each(data['items'], function(key, val){
            tegA = (start+key)+'.<a target="_blank" title="'+escapeHtml(val['title'])+'" href="'+val['link']+'"><img alt="'+escapeHtml(val['title'])+'" class="preview-image" src="'+val['link']+'"></a>';
            checkbox = '<input class="css-checkbox" id="cb'+(start+key)+'" onclick="DownloadImgUrl.addImgToList(\''+val['link']+'\',\''+(start+key)+'\',\''+val['image']['contextLink']+'\');" type="checkbox"><label for="cb'+(start+key)+'" class="css-label margin5">Выбрать</label>';
            sizeImg = '<p>Размер: '+val['image']['width']+'x'+val['image']['height']+'</p>';
			inputTitleImg = '<input title="Заголовок страницы с данной картинкой" type="text" class="form-control input-style" value="'+escapeHtml(val['title'])+'">';
            inputLinkToOriginal = '<input title="Ссылка на эту картинку" type="text" class="form-control input-style" value="'+val['link']+'">';
            inputLinkPageImg = '<input title="Страница с этой картинкой" type="text" class="form-control input-style" value="'+val['image']['contextLink']+'">';
            html = '<div id="ib'+(start+key)+'" class="imageBlock img-thumbnail">'+tegA+sizeImg+inputTitleImg+inputLinkToOriginal+inputLinkPageImg+checkbox+'</div>';
            $("#iamgeList").append(html);
            findBlackDomain((start+key), val['link']);
        });
        numDownloadImgOnPage = start+data['items'].length;
    }
    
    function onAjaxSuccess(data) {
        try {
            var imageArr = JSON.parse(data);
            if(imageArr['error'] != undefined) {
                throw imageArr['error'];
            }
            var html;
            var tegImg;
    		var i = 0;
            $.each(imageArr, function(key, val){
                tegImg = '<img class="preview-image" src="'+val['url']+'?rand='+Math.round(new Date().getTime() / 1000) +i+'">';
                html = '<div class="imageBlock img-thumbnail">'+tegImg+'</div>';
                $("#downloadedImg").append(html);
    			i++;
            });
            for(i = 0; i < numDownloadImgOnPage; i++) {
                if($("#cb"+i).prop('checked')) {
                    $("#cb"+i).prop('checked', false);
                    delete arrList[i];
                }
            }
            $("#uploadIMG input").show();
            $("#uploadIMG .displayHide").hide();
        } catch (e) {
            $("#uploadIMG input").show();
            $("#uploadIMG .displayHide").hide();
            $("#downloadedImg").append('<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Не удалось загрузить картинки =(<p>'+e+'</p></div>');
        }
    }
    return {
        sendUrlImage: function() {
            if(Object.keys(arrList).length > 0) {
                var data = JSON.stringify(arrList);
                $.post(
                  "?q=send-download",
                  {
                    'imgUrl':data,
                    'folderName':$("#folderName").val(),
                    'keyword':linesKeyWord[currentLineKeyWords]
                  },
                  onAjaxSuccess
                );
                $("#uploadIMG input").hide();
                $("#uploadIMG .displayHide").show();
            }
        },
        addImgToList: function(url, idCB, oUrlSite) {
            if (url != undefined) {
                if($("#cb"+idCB).prop('checked')) {
					arrList[idCB] = {
						"id":idCB,
                        "url": url,
                        "oUrlSite": oUrlSite
                    };
                } else {
                    delete arrList[idCB];
                }
            } else {
                $("#"+idCB).attr('checked', false);
            }
        },
        getImg: function(start, newResult) {
            try {
                if (start === void 0) {
                    start = 1;
                }
                if(newResult != undefined) {
                    $("#downloadedImg").html('');
                    $("#folderName").val('');
    		        arrList = {};
    		    }
    		    var keyWord = linesKeyWord[currentLineKeyWords].replace(/,\s?/g, '|');
    		    var paramSearch = "";
    		    var q = keyWord;
    		    if(!(q.length > 0)) {
    		        throw 'Задан пустой поисковый запрос';
    		    }
    		    if($("#searchSettings").val() != null) {
    		        paramSearch += '&'+$("#searchSettings").val().join("&");
    		    }
    		    if($("#noSearchDomain").val() != null) {
    		        q += ' '+$("#noSearchDomain").val().join(" ");
    		    }
    		    if($("#blackDomains").prop('checked')) {
                    var jqXHR = $.ajax({
                        url: "black-domains.json",
                        async: false
                    });
                    q += ' -site:'+$.parseJSON(jqXHR.responseText).join(" -site:");
                }
    		    if($("#countryID").val() != null) {
    		        paramSearch += '&cr='+$("#countryID").val().join("|");
    		    }
    		    if($("#searchSite").val().length > 1) {
    		        paramSearch += '&siteSearch='+encodeURIComponent($("#searchSite").val());
    		    }
                var url = 'ajax-image-search.php?q='+encodeURIComponent(q)+'&start='+start+paramSearch;
    
                $.ajax({
                    url : url,
                    dataType : 'text',
                    success : function(data){
                    try {
                        var data = JSON.parse(data);
                        if(data['error'] != undefined) {
                            throw data['error'];
                        }
                          if(data['searchInformation']['totalResults'] > 0) {
                                printImg(start, data, newResult);
                                if(data["queries"]["nextPage"] != undefined) {
                                    $("#pageNav").html('<input type="button" class="btn btn-info" onclick="DownloadImgUrl.getImg('+data["queries"]["nextPage"][0]["startIndex"]+', undefined);" value="Показать ещё">');
                                } else {
                                    $("#pageNav").html('');
                                }
                            } else {
                                throw 'Не найдено ни одного изображения по запросу <b>'+linesKeyWord[currentLineKeyWords]+'</b>.';
                            }
                    } catch (e) {
                        $("#iamgeList").html('<div class="alert alert-warning">'+e+'</div>');
                    }
                    }, error : function(err){}
                });
            } catch (e) {
                $("#iamgeList").html('<div class="alert alert-warning">'+e+'</div>');
            }
        },
        initArrayKeyWords: function() {
            if (document.getElementById('textBlock')) {
                var html = document.getElementById("textBlock")
                linesKeyWord = html.value.split("\n");
                if(currentLineKeyWords >= linesKeyWord.length) {
                    currentLineKeyWords = 0;
                }
                if(currentLineKeyWords < 1) {
                    $('.previousLineKeyWords').text("");
                    $('.previousLineKeyWords').css('height', '30px');
                } else {
                    $('.previousLineKeyWords').css('height', 'none');
                    $('.previousLineKeyWords').text(linesKeyWord[currentLineKeyWords-1]);
                }
                $('.currentLineKeyWords').text(linesKeyWord[currentLineKeyWords]);
                if((currentLineKeyWords+1) < linesKeyWord.length) {
                    $('.nextLineKeyWords').text(linesKeyWord[currentLineKeyWords+1]);
                } else {
                    $('.nextLineKeyWords').text("");
                    $('.nextLineKeyWords').css('height', '30px');
                }
            }
        },
        previousLineBtn: function() {
            if(currentLineKeyWords > 0) {
                if((currentLineKeyWords-2) >= 0) {
                    $('.previousLineKeyWords').css('height', 'none');
                    $('.previousLineKeyWords').text(linesKeyWord[currentLineKeyWords-2]);
                } else {
                    $('.previousLineKeyWords').text("");
                    $('.previousLineKeyWords').css('height', '30px');
                }
                $('.currentLineKeyWords').text(linesKeyWord[currentLineKeyWords-1]);
                $('.nextLineKeyWords').text(linesKeyWord[currentLineKeyWords]);
                currentLineKeyWords--;
            }
        },
        nextLineBtn: function() {
            if((currentLineKeyWords+1) < linesKeyWord.length) {
                $('.previousLineKeyWords').text(linesKeyWord[currentLineKeyWords]);
                $('.currentLineKeyWords').text(linesKeyWord[currentLineKeyWords+1]);
                if((currentLineKeyWords+2) < linesKeyWord.length) {
                    $('.nextLineKeyWords').css('height', 'none');
                    $('.nextLineKeyWords').text(linesKeyWord[currentLineKeyWords+2]);
                } else {
                    $('.nextLineKeyWords').text("");
                    $('.nextLineKeyWords').css('height', '30px');
                }
                currentLineKeyWords++;
            }
        },
        printImages: function(page) {
            if (document.getElementById('downloaded-pictures')) {
                $.post(
                  "?q=get-json-imgs",
                  {
                    'page':page,
                    'number-imgs':$("#numImgOnPage").val()
                  },function(data) {
                      try {
                            var imageArr = JSON.parse(data);
                            if(imageArr['error']) {
                                $("#downloaded-pictures").html('<div class="alert alert-info">'+imageArr['error']+'</div>');
                            } else {
                                var html;
                                var tegA;
                                var inputImageLink;
                        		var inputLinkToOriginal;
                        		var inputLinkPageImg;
                        		var removeBtn;
                        		$("#downloaded-pictures").html('');
                                $.each(imageArr['img'], function(key, val) {
                                    tegA = '<a target="_blank" title="'+val['KeyWords']+'" href="'+val['ImageLink']+'"><img alt="'+val['KeyWords']+'" class="preview-image" src="'+val['ImageLink']+'"></a>';
                                    inputImageLink = '<input title="Ссылка на эту картинку" type="text" class="form-control input-style" value="'+location.protocol+'//'+location.hostname+val['ImageLink']+'">';
                                    inputLinkToOriginal = '<input title="Оригинальная ссылка на эту картинку" type="text" class="form-control input-style" value="'+val['OriginalLink']+'">';
                                    inputLinkPageImg = '<input title="Страница с этой картинкой" type="text" class="form-control input-style" value="'+val['LinkPageImg']+'">';
                                    removeBtn = "<button type=\"button\" data-toggle=\"modal\" data-target=\".bs-example-modal-sm\" onclick=\"DownloadImgUrl.removesImg('"+(key.toString()+val['RequestID'])+"', '"+val['RequestID']+"', '"+val['ImageLink']+"')\" class=\"btn btn-danger\">Удалить</button>";
                                    html = '<div id="cb'+(key.toString()+val['RequestID'])+'" class="imageBlock img-thumbnail">'+tegA+inputImageLink+inputLinkToOriginal+inputLinkPageImg+removeBtn+'</div>';
                                    $("#downloaded-pictures").append(html);
                                });
                                printPagination(page, imageArr['countPages']);
                            }
                        } catch (e) {
                            $("#downloaded-pictures").html('<div class="alert alert-danger">Не удалось загрузить картинки =(</div>');
                        }
                  }
                );
            }
        },
        removesImg: function(id, requestKeyWordsId, imgURL) {
            if (confirm('Вы действительно хотите удалить это изображение?')
                && (imgURL != undefined) && (requestKeyWordsId != undefined)) {
                 $.post(
                    "index.php?q=removes-img",
                    {
                        'request-key-words-id': requestKeyWordsId,
                        'img-url': imgURL
                    }, function (data) {
                        var data = JSON.parse(data);
                        if(data['status']) {
                            $("#cb"+id).remove();
                        } else {
                            alert('Не удалось удалить картинку');
                        }
                    }
                );
            }
        }
    }
}());
$(document).ready(function(){
    var h_hght = 240;
   $(window).scroll(function(){
        var top = $(this).scrollTop();
        if (top > 100){
        	$("#back-top").fadeIn();
        } else{
        	$("#back-top").fadeOut();
        }
        var elem = $('#topNav');
        if (top < h_hght) {
            elem.css('display', 'none');
        } else {
            elem.css('display', 'block');
        }
    });
    $("#back-top").click(function (){
        $("body,html").animate({
            scrollTop:0
        }, 800);
        return false;
    });
    DownloadImgUrl.printImages(1, 10);
    DownloadImgUrl.initArrayKeyWords();
});