window.GUI = {
    MpdState: 0,
    SpopState: 0,
    cmd: 'status',
    playlist: {},
    currentsong: { Artist: "", Title: "", state: "" },
    currentknob: null,
    currentpath: '',
    halt: 0,
    volume: null,
    currentDBpos: new Array(0,0,0,0,0,0,0,0,0,0,0),
    DBentry: new Array('', '', '', '', '', ''), // path, x, y, title, artist, album
    visibility: 'visible',
    DBupdate: 0,
    browse: {
        isLibrary: false,
        files: [],
        mpdDirectories: [],
        spotifyTracks: [],
        spotifyDirectories: [],
        pandoraSongs: [],
        pandoraDirectories: []
    },
    library: {
        showLibrary: false
    }
};

module.exports = {
    state: window.GUI
}