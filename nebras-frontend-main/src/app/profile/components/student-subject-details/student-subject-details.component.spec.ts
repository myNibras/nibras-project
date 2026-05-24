import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StudentSubjectDetailsComponent } from './student-subject-details.component';

describe('StudentSubjectDetailsComponent', () => {
  let component: StudentSubjectDetailsComponent;
  let fixture: ComponentFixture<StudentSubjectDetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [StudentSubjectDetailsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StudentSubjectDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
