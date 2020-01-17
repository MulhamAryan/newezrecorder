function playM3u8(url,videoid){
    var video = document.getElementById(videoid);

    if(Hls.isSupported()) {
        var hls = new Hls();
        var m3u8Url = decodeURIComponent(url);
        hls.loadSource(m3u8Url);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED,function() {
            $(videoid).get(0).play();
        });
        document.title = url
    }
    else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
        video.addEventListener('canplay',function() {
            $(videoid).get(0).play();
        });
        video.volume = 0;
        document.title = url;
    }
}