export type WheelKey = 'question' | 'challenge';

export type WheelSegment = {
    /** Number printed on the segment. */
    position: number;
    /** Already landed on this round; it won't come up again until the round resets. */
    used: boolean;
};

export type WheelState = {
    key: WheelKey;
    /** "Question" or "Challenge". */
    label: string;
    segments: WheelSegment[];
};

export type SpinResult = {
    id: number;
    wheel: WheelKey;
    position: number;
    /** e.g. "Question 5". */
    title: string;
    body: string;
};

export type SpinResponse = {
    spin: SpinResult;
    wheel: WheelState;
};

export type PlayerRow = {
    name: string;
    score: number;
};

export type ResultRow = {
    rank: number;
    name: string;
    score: number;
};
