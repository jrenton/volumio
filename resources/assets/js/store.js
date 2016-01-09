window.GUI = {
    MpdState: 0,
    SpopState: 0,
    cmd: 'status',
    playlist: {
        songs: []
    },
    repeat: false,
    consume: false,
    random: false,
    single: false,
    currentsong: { 
        id: "", 
        artist: "", 
        title: "", 
        state: "", 
        album: "", 
        type: "", 
        time: 0, 
        elapsed: 0, 
        volume: 100, 
        percentcomplete: 0, 
        rating: ""
    },
    currentknob: null,
    currentpath: '',
    halt: 0,
    volume: null,
    currentDBpos: new Array(0,0,0,0,0,0,0,0,0,0,0),
    DBentry: new Array('', '', '', '', '', ''), // path, x, y, title, artist, album
    visibility: 'visible',
    DBupdate: 0,
    browse: {
        currentView: "default",
        isLibrary: false,
        files: [],
        directories: [],
        mpdDirectories: [],
        spotifyTracks: [],
        spotifyDirectories: [],
        pandoraSongs: [],
        pandoraDirectories: []
    },
    library: {
        showLibrary: false
    },
    queue: {
        songs: []
    },
    searchResults: []
};

module.exports = {
    state: window.GUI
}